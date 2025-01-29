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
use Exceptions\MethodNotAllowedException;
use Exceptions\NotFoundException;
use Exceptions\ServerException;
use Exceptions\UnauthorizedException;
use Exceptions\UserNotVerifiedException;
use Model\Token;
use Utilities\Uid;

class Cats {
    /**
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     * @throws ServerException
     */
    static function handler(array $uriParts): string {
        $uriPartsCount = sizeof($uriParts);

        if ($uriPartsCount == 1) {
            return self::listAllCats();
        } elseif ($uriPartsCount >= 2) {
            if (strlen($uriParts[1]) == 32 + 4 || strlen($uriParts[1]) == 32) return self::handleUidURI($uriParts);

            return match ($uriParts[1]) {
                "create" => self::create(),
                default => throw new NotFoundException()
            };
        } else throw new NotFoundException();
    }

    /**
     * Retrieves a list of all cats as a JSON-encoded string.
     *
     * This method handles the GET HTTP method for listing all cats from the database.
     * Pagination can be controlled via the optional query parameters 'page' and 'items'
     * for specifying the current page number and the number of items per page, respectively.
     *
     * @return string JSON-encoded string containing the list of all cats.
     * @throws MethodNotAllowedException If the request method is not GET.
     * @throws ServerException If an error occurs during the database operation or server error.
     */
    static private function listAllCats(): string {
        if ($_SERVER['REQUEST_METHOD'] != 'GET')
            throw new MethodNotAllowedException($_SERVER['REQUEST_METHOD']);

        $page = $_GET['page'] ?? null;
        $itemsPerPage = $_GET['items'] ?? 25;

        try {
            GenericDAO::connect();
            $cats = CatDAO::readAll();
            GenericDAO::disconnect();
        } catch (Exception $ex) {
            throw new ServerException(
               message: $ex->getMessage(),
               trace: $ex->getTrace(),
               thrownIn: "\BaseHandlers\Cats::listAllCats()"
            );
        }
        if (sizeof($cats) == 0) return "[]";

        $jsonCats = [];

        foreach ($cats as $cat) {
            $jsonCats[] = $cat->toJson();
        }

        return json_encode($jsonCats);
    }

    static private function create(): string {

    }

    /**
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     * @throws ServerException
     */
    static private function handleUidURI(array $uriParts): string {
        if (!Uid::verify($uriParts[1])) throw new NotFoundException(NotFoundReason::CAT_NOT_FOUND);

        if (sizeof($uriParts) >= 3) {
            throw new NotFoundException(NotFoundReason::PATH_NOT_FOUND);
        }

        $requestMethod = $_SERVER['REQUEST_METHOD'];

        return match ($requestMethod) {
            'GET' => self::getCat($uriParts),
            'PUT' => self::updateCat($uriParts),
            'DELETE' => self::deleteCat($uriParts),
            default => throw new MethodNotAllowedException($requestMethod)
        };
    }

    /**
     * @throws NotFoundException
     * @throws ServerException
     */
    static private function getCat(array $uriParts): string {
        try {
            GenericDAO::connect();
            if (!CatDAO::doesCatExist($uriParts[1])) {
                GenericDAO::disconnect();
                throw new NotFoundException(NotFoundReason::CAT_NOT_FOUND);
            }

            $cat = CatDAO::read($uriParts[1]);
            GenericDAO::disconnect();
        } catch (NotFoundException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new ServerException(
               message: $ex->getMessage(),
               trace: $ex->getTrace(),
               thrownIn: "\BaseHandlers\Cats::getCat()"
            );
        }

        return json_encode($cat->toJson());
    }

    /**
     * @throws NotFoundException
     * @throws ServerException
     * @throws UnauthorizedException
     * @throws UserNotVerifiedException
     */
    static private function updateCat(array $uriParts): string {
        $json = json_decode(file_get_contents("php://input"), true);

        try {
            $bearerToken = Token::getTokenFromHeader();

            RedisDb::connect();
            GenericDAO::connect();

            $authenticatedUser = RedisDb::validateUserToken($bearerToken, $uriParts[1]);

            if (UserDAO::read($authenticatedUser) == null) throw new NotFoundException(NotFoundReason::USER_NOT_FOUND);
            if (!UserDAO::isUserAccountConfirmed($authenticatedUser)) throw new UserNotVerifiedException();

            $cat = CatDAO::read($uriParts[1]);

            if (!$cat) throw new NotFoundException(NotFoundReason::CAT_NOT_FOUND);

            $catOwner = Uid::compact($cat->getOwnerUID());

            if ($catOwner == null || strlen($catOwner != 32) || strcmp($catOwner, $authenticatedUser) != 0)
                throw new UnauthorizedException(UnauthorizedReason::NOT_ALLOWED);

            if (array_key_exists('age', $json)) {
                $cat->setAge($json['age']);
            }
            if (array_key_exists('description', $json)) {
                $cat->setDescription($json['description']);
            }
            if (array_key_exists('whenLastSeen', $json)) {
                $cat->setWhenLastSeen($json['whenLastSeen']);
            }
            if (array_key_exists("whereLastSeen", $json)) {
                $cat->setWhereLastSeen($json["whereLastSeen"]);
            }
            if (array_key_exists("weight", $json)) {
                $cat->setWeight($json["weight"]);
            }
            if (array_key_exists("isStray", $json)) {
                $cat->setIsStray($json["isStray"]);
            }

            CatDAO::update($cat);
            GenericDAO::disconnect();
        } catch (UnauthorizedException|UserNotVerifiedException|NotFoundException $ex) {
            GenericDAO::disconnect();
            throw $ex;
        } catch (Exception $ex) {
            GenericDAO::disconnect();
            throw new ServerException(
               message: $ex->getMessage(),
               code: $ex->getCode(),
               trace: $ex->getTrace(),
               thrownIn: "\BaseHandlers\Users::updateUser()"
            );
        }

        return json_encode($cat->toJson());
    }

    /**
     * @throws NotFoundException
     * @throws ServerException
     * @throws UnauthorizedException
     * @throws UserNotVerifiedException
     */
    static private function deleteCat(array $uriParts): string {
        try {
            $bearerToken = Token::getTokenFromHeader();

            RedisDb::connect();
            GenericDAO::connect();

            $authenticatedUser = RedisDb::validateUserToken($bearerToken, $uriParts[1]);

            if (UserDAO::read($authenticatedUser) == null) throw new NotFoundException(NotFoundReason::USER_NOT_FOUND);
            if (!UserDAO::isUserAccountConfirmed($authenticatedUser)) throw new UserNotVerifiedException();

            $cat = CatDAO::read($uriParts[1]);

            if (!$cat) throw new NotFoundException(NotFoundReason::CAT_NOT_FOUND);

            CatDAO::delete($uriParts[1]);
        } catch (UnauthorizedException|UserNotVerifiedException|NotFoundException $ex) {
            GenericDAO::disconnect();
            throw $ex;
        } catch (Exception $ex) {
            GenericDAO::disconnect();
            throw new ServerException(
               message: $ex->getMessage(),
               code: $ex->getCode(),
               trace: $ex->getTrace(),
               thrownIn: "\BaseHandlers\Users::updateUser()"
            );
        }

        return '{"success": true}';
    }
}
