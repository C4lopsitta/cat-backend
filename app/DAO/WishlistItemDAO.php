<?php

namespace DAO;

use Model\Cat;
use DAO\GenericDAO;
use Random\RandomException;
use Utilities\Uid;

use PDO;
use Exception;

class WishlistItemDAO extends GenericDAO
{

    /**
     * @throws RandomException
     */
    public static function create(object $object): ?object
    {
        $sql = "INSERT INTO wishListItems(uid, owner, cat)
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

    /**
     * @throws Exception
     */
    public static function read(int $id): ?object
    {
        throw new Exception('Not implemented');
    }

    /**
     * @throws Exception
     */
    public static function readAll(): ?array
    {
        throw new Exception('Not implemented');
    }

    public static function readAllWishlistItems(): ?array
    {
        $sql = "SELECT cats.* FROM wishListItems, cats
                        WHERE cats.uid = wishListItems.cat;";

        $resultSet = self::$pdo->query($sql);
        $results = $resultSet->fetchAll();

        $cats = array();
        foreach ($results as $result) {
            $cats[] = new Cat($result["uid"], $result["name"], $result["age"], $result["description"], $result["whenLastSeen"],
                $result["race"], $result["furColor"], $result["weight"], $result["image"], $result["imageMimeType"],
                $result["price"], $result["owner"]);
        }

        return $cats;
    }

    /**
     * @throws Exception
     */
    public static function update(object $object): bool
    {
        throw new Exception('Not implemented');
    }

    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM wishListItems 
                        WHERE wishListItems.uid = :id;";

        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);

    }
}