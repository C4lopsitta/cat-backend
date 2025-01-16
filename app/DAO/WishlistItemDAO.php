<?php

namespace DAO;
use Cat;
use GenericDAO;

class WishlistItemDAO extends GenericDAO
{

    public function create(object $object): ?object
    {
        try {
            $sql = "INSERT INTO wishListItems(uid, owner, cat)
                        VALUES (:id, :owner, :cat)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => UUID . genUuid(),
                ':owner' => $object->getOwner(),
                ':cat' => $object->getCat()
            ]);

            $id = $this->pdo->lastInsertId();
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
    public function read(int $id): ?object
    {
        throw new Exception('Not implemented');
    }

    /**
     * @throws Exception
     */
    public function readAll(): ?array
    {
        throw new Exception('Not implemented');
    }

    public function readAllWishlistItems(): ?array
    {
        try {
            $sql = "SELECT cats.* FROM wishListItems, cats
                        WHERE cats.uid = wishListItems.cat;";

            $resultSet = $this->pdo->query($sql);
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
    public function update(object $object): bool
    {
        throw new Exception('Not implemented');
    }

    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM wishListItems 
                        WHERE wishListItems.uid = :id;";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);

        } catch (PDOException $e) {
            echo($e->getMessage());
            return false;
        }
    }
}