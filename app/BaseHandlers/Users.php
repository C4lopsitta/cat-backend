<?php

namespace BaseHandlers;

use DAO\GenericDAO;
use DAO\RedisDb;
use DAO\UserDAO;
use Model\Token;
use Model\User;
use mysql_xdevapi\Exception;
use Utilities\CommonJsons;
use Utilities\Emails\ConfirmRegister;
use Utilities\MailSender;
use Utilities\Password;
use Utilities\Regexes;
use Utilities\Uid;

class Users {
    static function handler(array $uriParts): void {
        // list all users (w/o private information)
        if(sizeof($uriParts) == 1) {
            if($_SERVER['REQUEST_METHOD'] == 'GET') {
                $page = $_GET['page'] ?? null;
                $itemsPerPage = $_GET['items'] ?? 25;

                try {
                    GenericDAO::connect();
                    $users = UserDAO::readAll();
                    GenericDAO::disconnect();
                } catch (\Exception $ex) {
                    http_response_code(500);
                    echo CommonJsons::ServerError($ex);
                    return;
                }

                echo \Jsons\Users::listUsers($users, $page, $itemsPerPage);
            } else {
                http_response_code(405);
                echo CommonJsons::$MethodNotAllowed;
            }
            return;
        }

        if(sizeof($uriParts) >= 2) {
            if($uriParts[1] == "register") {
                self::handleRegistration($uriParts);
                return;
            } elseif($uriParts[1] == "authenticate") {
                self::handleAuthenticate($uriParts);
                return;
            } elseif(strlen($uriParts[1]) == 32 + 4) {
                Users::handleUidURI($uriParts);
            } else {
                http_response_code(404);
                echo CommonJsons::$NotFound;
                return;
            }
        }

        http_response_code(404);
        echo CommonJsons::$NotFound;
    }

    private static function handleUidURI(array $uriParts): void {
        if (!Uid::verify($uriParts[1])) {
            http_response_code(400);
            echo CommonJsons::$InvalidUID;
            return;
        }elseif (Uid::verify($uriParts[1])) {
            if (sizeof($uriParts) == 3) {
                if($uriParts[2] == "validate") {
                    self::validateAccount($uriParts);
                }
                // uid + some action
                return;
            }else {
                // only uid was given
                return;
            }
        }
    }

    // --- //

    private static function handleRegistration(array $uriParts): void {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $reqJson = json_decode(file_get_contents('php://input'), true);

            $email = $reqJson['email'] ?? null;
            $password = $reqJson['password'] ?? null;
            $username = $reqJson['username'] ?? null;
            $description = $reqJson['description'] ?? null;
            $pronouns = $reqJson['pronouns'] ?? null;

            $fieldErrors = [];

            // TODO)) Fix regexp
//            if($email == null || !preg_match(Regexes::$Email, $email)) { $fieldErrors[] = "email"; }
//            if($password == null || !preg_match(Regexes::$Password, $password)) { $fieldErrors[] = "password"; }
//            if($username == null || !preg_match(Regexes::$Username, $username)) { $fieldErrors[] = "username"; }

            if(sizeof($fieldErrors) > 0) {
                http_response_code(400);
                echo CommonJsons::BadRequest($fieldErrors);
                return;
            }

            // TODO)) Add email_already_in_use error
            if(false) {
                http_response_code(401);
                echo "todo response email in use";
                return;
            }

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
            } catch (\Exception $ex) {
                if($ex->getCode() == 23000) {
                   http_response_code(401);
                   echo \Jsons\Users::userExistsResponse($email);
                } else {
                    http_response_code(500);
                    echo CommonJsons::ServerError($ex);
                }
                return;
            }

            $emailConfirmationBaseUrl = $reqJson['emailConfirmationBaseUrl'] ?? "http://" . getenv("SERVER_ADDRESS") . "/api/v1/users/". Uid::format($user->getUid()) . "/validate";

            try {
                RedisDb::connect();
            } catch(\Exception $ex) {
                http_response_code(500);
                echo CommonJsons::ServerError($ex);
                return;
            }

            $confirmationIdToken = RedisDb::generateAndStoreAccountConfirmToken($user->getUid());

            try {
                MailSender::send(
                   html: ConfirmRegister::html($username, $emailConfirmationBaseUrl, $confirmationIdToken, $user->getUid()),
                   text: ConfirmRegister::plainText($username, $emailConfirmationBaseUrl, $confirmationIdToken, $user->getUid()),
                   subject: "Kittens - Confirm your Account",
                   emailDest: $email
                );
            } catch (\Exception $ex) {
                http_response_code(500);
                echo CommonJsons::ServerError($ex);
                UserDAO::connect();
                UserDAO::delete($user->getUid());
                UserDAO::disconnect();
                return;
            }

            echo \Jsons\Users::userRegistrationResponse(username: $username, email: $email, uid: Uid::format($user->getUid()));
        } else {
            http_response_code(405);
            echo CommonJsons::$MethodNotAllowed;
        }
    }

    private static function validateAccount(array $uriParts): void {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $json = json_decode(file_get_contents('php://input'), true);

            $userUid = $uriParts[1];
            $confirmationId = $json['confirmationId'] ?? null;

            if($confirmationId == null) {
                http_response_code(400);
                echo CommonJsons::BadRequest(["confirmationId"]);
                return;
            }

            try {
                RedisDb::connect();
            } catch(\Exception $ex) {
                http_response_code(500);
                echo CommonJsons::ServerError($ex);
                return;
            }

            $redisUserUid = RedisDb::verifyAccountConfirmToken($confirmationId);

            if($redisUserUid == null || strlen($redisUserUid) < 1) {
                http_response_code(401);
                // TODO)) Token expired or doesnt exist
                return;
            }

            if(Uid::compact($redisUserUid) == Uid::compact($userUid)) {
                try {
                    GenericDAO::connect();
                    $user = UserDAO::read($redisUserUid);
                    GenericDAO::disconnect();

                    if($user == null) {
                        throw new \Exception("User does not exist");
                    }

                    if($user->isAccountConfirmed()) {
                        http_response_code(208);
                        RedisDb::invalidateUserTokens($user->getUid());
                    }

                    $newUserToken = Token::generate($user->getUid());

                    RedisDb::storeUserToken($newUserToken->getToken(), $user->getUid());

                    echo \Jsons\Users::newUserTokenResponse($newUserToken->getToken(), 3600);

                } catch (\Exception $ex) {
                    http_response_code(500);
                    echo CommonJsons::ServerError($ex);
                    return;
                }
            }

            http_response_code(400);
            echo CommonJsons::BadRequest(["confirmationId"]);
        } else {
            http_response_code(405);
            echo CommonJsons::$MethodNotAllowed;
        }
    }

    private static function handleAuthenticate(array $uriParts): void {

    }
}
