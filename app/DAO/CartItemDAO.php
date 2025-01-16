<?php

namespace DAO;

use Model\Cat;
use DAO\GenericDAO;

use PDO;
use Exception;

class CartItemDAO extends GenericDAO
{

    public static function create(object $object): ?object
    {
        try {
            $sql = "INSERT INTO cartItems(uid, owner, cat)
                        VALUES (:id, :owner, :cat)";

            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([
                ':id' => UUID . genUuid(),
                ':owner' => $object->getOwner(),
                ':cat' => $object->getCat()
            ]);

            $id = self::$pdo->lastInsertId();
            $object->setUid($id);

            return $object;
        } catch (PDOException $e) {
            echo($e->getMessage());
            return null;
        }
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

    public static function readAllCartItems(): ?array
    {
        try {
            $sql = "SELECT cats.* FROM cartItems, cats
                        WHERE cats.uid = cartItems.cat;";

            $resultSet = self::$pdo->query($sql);
            $results = $resultSet->fetchAll();

            $cats = array();
            foreach ($results as $result) {
                $cats[] = new Cat($result["uid"], $result["name"], $result["age"], $result["description"], $result["whenLastSeen"],
                    $result["race"], $result["furColor"], $result["weight"], $result["image"], $result["imageMimeType"],
                    $result["price"], $result["owner"]);
            }

            return $cats;
        } catch (PDOException $e) {
            echo($e->getMessage());
            return null;
        }
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
        try {
            $sql = "DELETE FROM cartItems 
                        WHERE cartItems.uid = :id;";

            $stmt = self::$pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);

        } catch (PDOException $e) {
            echo($e->getMessage());
            return false;
        }
    }
}