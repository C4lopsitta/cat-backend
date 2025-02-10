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

namespace DAO;

use Model\Cat;
use Random\RandomException;
use Utilities\Uid;

use PDO;
use Exception;

class CartItemDAO extends GenericDAO {

    /**
     * @throws RandomException
     */
    public static function create(object $object): ?object {
        $sql = "INSERT INTO cartItems(uid, owner, cat)
                        VALUES (:id, :owner, :cat)";

        $uid = Uid::compact(Uid::generate());

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':id' => $uid,
            ':owner' => $object->getOwner(),
            ':cat' => $object->getCat()
        ]);

        $object->setUid($uid);

        return $object;
    }

    public static function getUserCartAsUIDs(string $uid): array {
        $uid = Uid::compact($uid);

        $sql = "SELECT cats.uid FROM cartItems, cats WHERE cartItems.owner = :idOwner AND cartItems.cat = cats.uid;";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':idOwner' => $uid]);
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        $catUids = array();
        foreach ($results as $result) {
            $catUids[] = Uid::format($result->uid);
        }

        return $catUids;
    }


    public static function getUserCart(string $uid): array {
        $uid = Uid::compact($uid);

        $sql = "SELECT cats.* FROM cartItems, cats WHERE cartItems.owner = :idOwner AND cartItems.cat = cats.uid;";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':idOwner' => $uid]);
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        $cats = array();
        foreach ($results as $result) {
            $cats[] = new Cat($result->name, $result->age, $result->description, $result->whenLastSeen,
                $result->whereLastSeen, $result->race, $result->furColor, $result->weight, $result->isStray, $result->image, $result->imageMimeType,
                $result->price, $result->owner, $result->uid);
        }

        return $cats;
    }

    /**
     * @throws Exception
     */
    public static function read(string $id): ?object {
        throw new Exception('Not implemented');
    }

    /**
     * @throws Exception
     */
    public static function readAll(): ?array {
        throw new Exception('Not implemented');
    }

    /**
     * @throws Exception
     */
    public static function update(object $object): bool {
        throw new Exception('Not implemented');
    }

    public static function delete(string $id): bool {
        $sql = "DELETE FROM cartItems 
                        WHERE cartItems.uid = :id;";

        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}