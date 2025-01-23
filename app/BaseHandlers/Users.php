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

class Users {
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
     * @throws MethodNotAllowedException
     * @throws BadRequestException
     * @throws UserAlreadyExistsException
     * @throws ServerException
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

        // TODO)) Fix regexp
        if($email == null) { $fieldErrors[] = "email"; }
//               || !preg_match(Regexes::$Email, $email))
        if($password == null) { $fieldErrors[] = "password"; }
//               || !preg_match(Regexes::$Password, $password))
        if($username == null) { $fieldErrors[] = "username"; }
//               || !preg_match(Regexes::$Username, $username))

        if (sizeof($fieldErrors) > 0) throw new BadRequestException($fieldErrors);

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
     * @throws MethodNotAllowedException
     * @throws ServerException
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
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     * @throws ServerException
     * @throws UnauthorizedException
     * @throws BadRequestException
     * @throws UserNotVerifiedException
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
     * @throws MethodNotAllowedException
     * @throws BadRequestException
     * @throws ServerException
     * @throws NotFoundException
     * @throws UnauthorizedException
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
     * @throws ServerException
     * @throws UserNotVerifiedException
     * @throws NotFoundException
     */
    private static function getUser(array $uriParts): string {
        try {
            GenericDAO::connect();
            if(!UserDAO::doesUserExist($uriParts[1])) {
                GenericDAO::disconnect();
                throw new UserNotVerifiedException("User account is not verified.");
            }

            $user = UserDAO::read($uriParts[1]);
            GenericDAO::disconnect();

            if ($user == null) throw new NotFoundException(NotFoundReason::USER_NOT_FOUND);

            return \Jsons\Users::user($user);
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
     * @throws UnauthorizedException
     * @throws ServerException
     * @throws UserNotVerifiedException
     * @throws NotFoundException
     */
    private static function updateUser(array $uriParts): string {
        $json = json_decode(file_get_contents('php://input'), true);

        try {
            $bearerToken = Token::getTokenFromHeader();

            RedisDb::connect();
            GenericDAO::connect();

            RedisDb::validateUserToken($bearerToken, $uriParts[1]);

            if(!UserDAO::doesUserExist($uriParts[1])) throw new NotFoundException(NotFoundReason::USER_NOT_FOUND);
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
     * @throws ServerException
     * @throws UnauthorizedException
     * @throws NotFoundException
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
