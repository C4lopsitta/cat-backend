<?php
/*
 * Copyright (c) 2025.
 *
 * Code is licensed under GNU GPLv3 License and available in the Copying file.
 * Code was written by:
 * - Simone Robaldo ( simone.robaldo at itiscuneo.eu )
 * - Giulia Vadelli ( giulia.vadelli at itiscuneo.eu )
 * - Daniele Torchio ( daniele.torchio at itiscuneo.eu )
 */

namespace BaseHandlers;

use DAO\CatDAO;
use DAO\GenericDAO;
use DAO\RedisDb;
use DAO\UserDAO;
use Enums\NotFoundReason;
use Enums\UnauthorizedReason;
use Exception;
use Exceptions\BadRequestException;
use Exceptions\MethodNotAllowedException;
use Exceptions\NotFoundException;
use Exceptions\ServerException;
use Exceptions\UnauthorizedException;
use Exceptions\UserAlreadyExistsException;
use Exceptions\UserNotVerifiedException;
use Model\Token;
use Model\User;
use Utilities\Emails\ConfirmRegister;
use Utilities\MailSender;
use Utilities\Password;
use Utilities\Uid;

/**
 * Main handler for path `/api/v1/users`. Handles all paths inside and the root path with other internal private methods.
 * @param array $uriParts The URL's path in array form
 * @return string The JSON string that will be shown to the User
 * @throws BadRequestException If the request was badly formatted
 * @throws UserNotVerifiedException If the requested user's account was not verified
 * @throws NotFoundException If the requested path could not be found
 * @throws UnauthorizedException If the authorization was not provided correctly or the token has expired
 * @throws UserAlreadyExistsException If the user creation request is for an email address that already exists
 * @throws MethodNotAllowedException If the requested method for the given path is not allowed
 * @throws ServerException If a fatal server error has happened
 */
class Users {
    /**
     * Main handler for path `/api/v1/users`. Handles all paths inside and the root path with other internal private methods.
     * @param array $uriParts The URL's path in array form
     * @return string The JSON string that will be shown to the User
     * @throws BadRequestException If the request was badly formatted
     * @throws NotFoundException If the requested path could not be found
     * @throws UnauthorizedException If the authorization was not provided correctly or the token has expired
     * @throws UserNotVerifiedException When the user account was not verified
     * @throws UserAlreadyExistsException If the user creation request is for an email address that already exists
     * @throws MethodNotAllowedException If the requested method for the given path is not allowed
     * @throws ServerException If a fatal server error has happened
     */
    static function handler(array $uriParts): string {
        $uriPartsCount = sizeof($uriParts);
        if($uriPartsCount == 1) {
            return self::listAllUsers();
        } elseif($uriPartsCount >= 2) {
            if(strlen($uriParts[1]) == 32 + 4 || strlen($uriParts[1]) == 32) return Users::handleUidURI($uriParts);

            return match ($uriParts[1]) {
                "register" => self::handleRegistration(),
                "authenticate" => self::handleAuthenticate(),
                default => throw new NotFoundException(),
            };
        } else throw new NotFoundException();
    }

    /**
     * Returns a JSON formatted list of users when a GET request is made
     * @throws ServerException
     * @throws MethodNotAllowedException
     */
    private static function listAllUsers(): string {
        if($_SERVER['REQUEST_METHOD'] != 'GET') {
            throw new MethodNotAllowedException($_SERVER['REQUEST_METHOD']);
        }

        $page = $_GET['page'] ?? null;
        $itemsPerPage = $_GET['items'] ?? 25;

        try {
            GenericDAO::connect();
            $users = UserDAO::readAll();
            GenericDAO::disconnect();
        } catch (Exception $ex) {
            throw new ServerException(
               message: $ex->getMessage(),
               trace: $ex->getTrace(),
               thrownIn: "\BaseHandlers\Users::listAllUsers()"
            );
        }

        return \Jsons\Users::listUsers($users, $page, $itemsPerPage);
    }

    /**
     * Handles the user registration process.
     *
     * This method verifies the incoming JSON payload from a POST request, validates the provided
     * registration data (email, password, and username), and attempts to register a new user in the system.
     * If the registration is successful, it sends an email confirmation and returns a JSON response
     * with the registered user's basic information.
     *
     * @return string JSON-encoded string containing a response with the registered user data.
     * @throws MethodNotAllowedException If the HTTP request method is not POST.
     * @throws BadRequestException If required fields are missing or fail validation checks.
     * @throws UserAlreadyExistsException If the email or username is already registered.
     * @throws ServerException If there is an issue with database operations or email sending.
     */
    private static function handleRegistration(): string {
        if($_SERVER["REQUEST_METHOD"] != "POST") {
            throw new MethodNotAllowedException($_SERVER["REQUEST_METHOD"]);
        }

        $reqJson = json_decode(file_get_contents('php://input'), true);

        $email = $reqJson['email'] ?? null;
        $password = $reqJson['password'] ?? null;
        $username = $reqJson['username'] ?? null;
        $description = $reqJson['description'] ?? null;
        $pronouns = $reqJson['pronouns'] ?? null;

        $fieldErrors = [];

        // Define regex patterns
        $emailRegex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'; // Valid email regex
        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{12,}$/'; // Password regex (good mix of complexity)

        if ($email == null || !preg_match($emailRegex, $email)) {
            $fieldErrors[] = "email";
        }
        if ($password == null || !preg_match($passwordRegex, $password)) {
            $fieldErrors[] = "password";
        }
        if ($username == null || strlen($username) < 4 || strlen($username) > 32) {
            $fieldErrors[] = "username";
        }        if (sizeof($fieldErrors) > 0) throw new BadRequestException($fieldErrors);

        $passwordHash = Password::hash($password);

        $user = new User(
           username: $username,
           uid: "",
           email: $email,
           image: null,
           imageMimeType: null,
           description: $description,
           pronouns: $pronouns,
           passwordHash: $passwordHash
        );

        try {
            GenericDAO::connect();
            UserDAO::create($user);
            GenericDAO::disconnect();
        } catch (Exception $ex) {
            if ($ex->getCode() == 23000) {
                throw new UserAlreadyExistsException();
            } else {
                throw new ServerException(
                   message: $ex->getMessage(),
                   code: $ex->getCode(),
                   trace: $ex->getTrace(),
                   thrownIn: "\BaseHandlers\Users::handleRegistration()"
                );
            }
        }

        $emailConfirmationBaseUrl = $reqJson['emailConfirmationBaseUrl'] ?? "http://" . getenv("SERVER_ADDRESS") . "/api/v1/users/" . Uid::format($user->getUid()) . "/validate";

        try {
            RedisDb::connect();
            $confirmationIdToken = RedisDb::generateAndStoreAccountConfirmToken($user->getUid());
        } catch (Exception $ex) {
            UserDAO::connect();
            UserDAO::delete($user->getUid());
            UserDAO::disconnect();
            throw new ServerException(
               message: $ex->getMessage(),
               code: $ex->getCode(),
               trace: $ex->getTrace(),
               thrownIn: "\BaseHandlers\Users::handleRegistration()"
            );
        }

        try {
            MailSender::send(
               html: ConfirmRegister::html($username, $emailConfirmationBaseUrl, $confirmationIdToken, $user->getUid()),
               text: ConfirmRegister::plainText($username, $emailConfirmationBaseUrl, $confirmationIdToken, $user->getUid()),
               subject: "Kittens - Confirm your Account",
               emailDest: $email
            );
        } catch (Exception $ex) {
            UserDAO::connect();
            UserDAO::delete($user->getUid());
            UserDAO::disconnect();
            throw new ServerException(
               message: $ex->getMessage(),
               code: $ex->getCode(),
               trace: $ex->getTrace(),
               thrownIn: "\BaseHandlers\Users::handleRegistration()"
            );
        }

        return \Jsons\Users::userRegistrationResponse(username: $username, email: $email, uid: Uid::format($user->getUid()));
    }

    /**
     * Authenticates a user by verifying their email and password, and generates an authentication token upon success.
     *
     * This method expects a POST request with a JSON payload containing the user's email and password.
     * It validates the credentials, checks for the user's existence in the database, and returns a token if authentication is successful.
     * Throws exceptions for invalid request methods, user not found, or other server errors.
     *
     * @return string A JSON response containing the user UID, the generated token, and the token expiry time (3600 seconds).
     * @throws MethodNotAllowedException If the HTTP request method is not POST.
     * @throws ServerException If an unexpected server error occurs.
     */
    private static function handleAuthenticate(): string {
        if($_SERVER["REQUEST_METHOD"] != "POST") {
            throw new MethodNotAllowedException($_SERVER["REQUEST_METHOD"]);
        }

        $json = json_decode(file_get_contents('php://input'), true);

        try {
            GenericDAO::connect();
            RedisDb::connect();

            if (!UserDAO::doesUserExist($json['email'])) {
                throw new NotFoundException(NotFoundReason::USER_NOT_FOUND);
            }

            $userUid = UserDAO::fetchUserUidFromEmail($json['email']);
            $password = $json['password'];

            $user = UserDAO::read($userUid);

            GenericDAO::disconnect();

            if (Password::verify($password, $user->getPasswordHash())) {
                // generate token
                $token = Token::generate($user->getUid());
                RedisDb::storeUserToken($token->getToken(), $user->getUid());

                return \Jsons\Users::newUserTokenResponse($user->getUid(), $token->getToken(), 3600);
            } else {
                throw new NotFoundException(NotFoundReason::USER_NOT_FOUND);
            }
        } catch (Exception $ex) {
            throw new ServerException(
               message: $ex->getMessage(),
               code: $ex->getCode(),
               trace: $ex->getTrace(),
               thrownIn: "\BaseHandlers\Users::handleAuthenticate()"
            );
        }
    }

    /**
     * Handles operations related to a UID-based URI path.
     *
     * @param array $uriParts The parts of the URI path. The second element is expected
     *                        to be a valid UID, and optionally the third element may indicate
     *                        a specific operation (e.g., "validate").
     * @return string A result from the corresponding operation, such as validation, fetching,
     *                updating, or deleting a user.
     * @throws NotFoundException If the UID is invalid or if the specified path does not exist.
     * @throws MethodNotAllowedException If the HTTP request method is not supported for the operation.
     * @throws ServerException When a critical server error happens
     * @throws BadRequestException When a badly formatted request is made
     * @throws UserNotVerifiedException When the user account is not verified
     * @throws UnauthorizedException When a request is made to an authenticated endpoint without proper authentication
     */
    private static function handleUidURI(array $uriParts): string {
        if (!Uid::verify($uriParts[1])) {
            throw new NotFoundException(NotFoundReason::USER_NOT_FOUND);
        } elseif (Uid::verify($uriParts[1])) {
            if (sizeof($uriParts) == 3) {
                if ($uriParts[2] == "validate") {
                    return self::validateAccount($uriParts);
                }
            } else {
                $requestMethod = $_SERVER['REQUEST_METHOD'];
                return match ($requestMethod) {
                    'GET' => self::getUser($uriParts),
                    'PUT' => self::updateUser($uriParts),
                    'DELETE' => self::deleteUser($uriParts),
                    default => throw new MethodNotAllowedException($requestMethod)
                };
            }
        }
        throw new NotFoundException(NotFoundReason::PATH_NOT_FOUND);
    }

    /**
     * Validates an account by confirming the provided confirmation ID and user UID.
     * It ensures the user exists, matches the confirmation token, and confirms the account if valid.
     *
     * @param array $uriParts The URI segments, where the second element contains the user UID.
     * @return string A JSON-encoded response containing the new user token and its details.
     *
     * @throws MethodNotAllowedException If the HTTP method is not POST.
     * @throws BadRequestException If the confirmation ID is missing or invalid.
     * @throws NotFoundException If the user does not exist.
     * @throws UnauthorizedException If the confirmation token is invalid or does not match the user UID.
     * @throws ServerException If an internal server error occurs during validation.
     */
    private static function validateAccount(array $uriParts): string {
        if($_SERVER["REQUEST_METHOD"] != "POST") {
            throw new MethodNotAllowedException($_SERVER["REQUEST_METHOD"]);
        }

        $json = json_decode(file_get_contents('php://input'), true);

        $userUid = $uriParts[1];
        $confirmationId = $json['confirmationId'] ?? null;

        if ($confirmationId == null) throw new BadRequestException(["confirmationId"]);

        // check if user exists
        try {
            RedisDb::connect();
            GenericDAO::connect();
            if(!UserDAO::doesUserExist($userUid)) throw new NotFoundException(NotFoundReason::USER_NOT_FOUND);
        } catch (NotFoundException $ex) { throw $ex; } catch (Exception $ex) {
            GenericDAO::disconnect();
            throw new ServerException(
               message: $ex->getMessage(),
               code: $ex->getCode(),
               trace: $ex->getTrace(),
               thrownIn: "\BaseHandlers\Users::validateAccount()"
            );
        }

        $redisUserUid = RedisDb::verifyAccountConfirmToken($confirmationId);

        if ($redisUserUid == null || strlen($redisUserUid) < 1) {
            GenericDAO::disconnect();
            throw new UnauthorizedException(UnauthorizedReason::INVALID_TOKEN);
        }

        if (Uid::compact($redisUserUid) != Uid::compact($userUid)) {
            GenericDAO::disconnect();
            throw new BadRequestException(["confirmationId"]);
        }

        try {
            $user = UserDAO::read($redisUserUid);
            GenericDAO::disconnect();

            if ($user == null) throw new NotFoundException(NotFoundReason::USER_NOT_FOUND);

            if ($user->isAccountConfirmed()) {
                http_response_code(208);
                RedisDb::invalidateUserTokens($user->getUid());
            }

            $newUserToken = Token::generate($user->getUid());
            RedisDb::storeUserToken($newUserToken->getToken(), $user->getUid());

            $user->setIsAccountConfirmed(true);
            GenericDAO::connect();
            UserDAO::update($user);
            GenericDAO::disconnect();

            return \Jsons\Users::newUserTokenResponse($user->getUid(), $newUserToken->getToken(), 3600);
        } catch (Exception $ex) {
            throw new ServerException(
               message: $ex->getMessage(),
               code: $ex->getCode(),
               trace: $ex->getTrace(),
               thrownIn: "\BaseHandlers\Users::validateAccount()"
            );
        }
    }
    // endregion userRegistration

    // region userRUD
    /**
     * Retrieves a user based on the provided URI parts.
     *
     * @param array $uriParts An array of URI segments where the second element corresponds to the user identifier.
     * @return string A JSON-encoded representation of the user.
     * @throws UserNotVerifiedException if the user account is not verified.
     * @throws NotFoundException if the user is not found.
     * @throws ServerException if an unexpected server error occurs.
     */
    private static function getUser(array $uriParts): string {
        try {
            GenericDAO::connect();
            if(!UserDAO::isUserAccountConfirmed($uriParts[1])) {
                GenericDAO::disconnect();
                throw new UserNotVerifiedException("User account is not verified.");
            }

            $user = UserDAO::read($uriParts[1]);

            if ($user == null) throw new NotFoundException(NotFoundReason::USER_NOT_FOUND);

            $userCats = CatDAO::readByOwnerUidList($uriParts[1]);
            GenericDAO::disconnect();

            return $user->toJson(ownedCats: $userCats);
        }  catch (UserNotVerifiedException|NotFoundException $ex) { throw $ex; } catch (Exception $ex) {
            GenericDAO::disconnect();
            throw new ServerException(
               message: $ex->getMessage(),
               code: $ex->getCode(),
               trace: $ex->getTrace(),
               thrownIn: "\BaseHandlers\Users::getUser()"
            );
        }
    }

    /**
     * Updates the details of a user based on the given parameters.
     *
     * @param array $uriParts The URI segments where the user identifier is expected at the second position.
     * @return string A JSON representation of the updated user data.
     *
     * @throws UnauthorizedException If the bearer token is invalid or unauthorized.
     * @throws UserNotVerifiedException If the user's account has not been confirmed.
     * @throws NotFoundException If the user does not exist.
     * @throws ServerException For generic server errors or unexpected exceptions.
     */
    private static function updateUser(array $uriParts): string {
        $json = json_decode(file_get_contents('php://input'), true);

        try {
            $bearerToken = Token::getTokenFromHeader();

            RedisDb::connect();
            GenericDAO::connect();

            RedisDb::validateUserToken($bearerToken, $uriParts[1]);

            if(UserDAO::read($uriParts[1] == null)) throw new NotFoundException(NotFoundReason::USER_NOT_FOUND);
            if(!UserDAO::isUserAccountConfirmed($uriParts[1])) throw new UserNotVerifiedException();

            $user = UserDAO::read($uriParts[1]);

            if (array_key_exists('description', $json)) {
                $user->setDescription($json['description']);
            }
            if (array_key_exists('pronouns', $json)) {
                $user->setPronouns($json['pronouns']);
            }
            if (array_key_exists('image', $json)) {
                $user->setImage($json['image']);
            }
            if (array_key_exists('image_mime', $json)) {
                $user->setImageMimeType($json['image_mime']);
            }

            UserDAO::update($user);
            GenericDAO::disconnect();
        } catch (UnauthorizedException|UserNotVerifiedException|NotFoundException $ex) { throw $ex; } catch (Exception $ex) {
            GenericDAO::disconnect();
            throw new ServerException(
               message: $ex->getMessage(),
               code: $ex->getCode(),
               trace: $ex->getTrace(),
               thrownIn: "\BaseHandlers\Users::updateUser()"
            );
        }
        return \Jsons\Users::user($user);
    }

    /**
     * Deletes a user from the system based on the provided user identifier in the URI.
     *
     * @param array $uriParts An array of URI segments where the second element represents the user identifier.
     * @return string A JSON-formatted string indicating the success of the operation.
     *
     * @throws UnauthorizedException If the token provided for the user is invalid or unauthorized.
     * @throws NotFoundException If the user does not exist.
     * @throws ServerException For any unexpected server errors during the operation.
     */
    private static function deleteUser(array $uriParts): string {
        try {
            $bearerToken = Token::getTokenFromHeader();
            RedisDb::connect();
            GenericDAO::connect();

            if(!UserDAO::doesUserExist($uriParts[1])) throw new NotFoundException(NotFoundReason::USER_NOT_FOUND);

            RedisDb::validateUserToken($bearerToken, $uriParts[1]);

            UserDAO::delete($uriParts[1]);
            GenericDAO::disconnect();
        } catch (UnauthorizedException|NotFoundException $ex) { throw $ex; } catch (Exception $ex) {
            GenericDAO::disconnect();
            throw new ServerException(
               message: $ex->getMessage(),
               code: $ex->getCode(),
               trace: $ex->getTrace(),
               thrownIn: "\BaseHandlers\Users::deleteUser()"
            );
        }
        return '{"success": true}';
    }
    // endregion userRUD
}
