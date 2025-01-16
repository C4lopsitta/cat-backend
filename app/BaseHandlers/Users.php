<?php

namespace BaseHandlers;

use DAO\GenericDAO;
use DAO\UserDAO;
use Model\User;
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

                echo \Jsons\Users::listUsers(["a", "b"], $page, $itemsPerPage);
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
            } elseif($uriParts[1] == "validateAccount") {
                self::validateAccount($uriParts);
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

            $emailConfirmationBaseUrl = $reqJson['emailConfirmationBaseUrl'] ?? null;

            try {
                MailSender::send(
                   html: ConfirmRegister::html($username, $emailConfirmationBaseUrl, ""),
                   text: ConfirmRegister::plainText($username, $emailConfirmationBaseUrl, ""),
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

    }

    private static function handleAuthenticate(array $uriParts): void {

    }
}
