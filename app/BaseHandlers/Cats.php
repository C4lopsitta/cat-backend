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
use Enums\NotFoundReason;
use Exception;
use Exceptions\MethodNotAllowedException;
use Exceptions\NotFoundException;
use Exceptions\ServerException;
use Utilities\Uid;

class Cats {
    /**
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     */
    static function handler(array $uriParts): string {
        $uriPartsCount = sizeof($uriParts);

        if ($uriPartsCount == 1) {
            return self::listAllCats();
        } elseif ($uriPartsCount >= 2) {
            if(strlen($uriParts[1]) == 32 + 4 || strlen($uriParts[1]) == 32) return self::handleUidURI($uriParts);

            return match ($uriParts[1]) {
                "create" => self::create(),
                default => throw new NotFoundException()
            };
        } else throw new NotFoundException();
    }

    /**
     * @throws MethodNotAllowedException
     * @throws ServerException
     */
    static private function listAllCats(): string {
        if($_SERVER['REQUEST_METHOD'] != 'GET')
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
        $jsonCats = [];

        foreach($cats as $cat) {
            $jsonCats[] = $cat->toJson();
        }

        return json_encode($jsonCats);
    }

    static private function create(): string {

    }

    /**
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     */
    static private function handleUidURI(array $uriParts): string {
        if (!Uid::verify($uriParts[1])) throw new NotFoundException(NotFoundReason::CAT_NOT_FOUND);

        if(sizeof($uriParts) >= 3) {
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

    static private function getCat(array $uriParts): string {

    }

    static private function updateCat(array $uriParts): string {

    }

    static private function deleteCat(array $uriParts): string {

    }
}
