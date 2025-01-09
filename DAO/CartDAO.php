<?php

class CartDAO extends GenericDAO
{

    public function create(object $object): ?object
    {
        try{
            $sql = "INSERT INTO cartItems(uid, owner, cat)
                        VALUES (:id, :owner, :cat)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => UUID.genUuid(),
                ':owner' => $object->getOwner(),
                ':cat' => $object->getCat()
            ]);

            $id = $this->pdo->lastInsertId();
            $object->setUid($id);

            return $object;
        }catch (PDOException $e){
            echo($e->getMessage());
            return null;
        }
    }

    public function read(int $id): ?object
    {
        throw new Exception('Not implemented');
    }

    public function readAll(): ?array
    {
        throw new Exception('Not implemented');
    }

    public function readAllCartItems(): ?array
    {
        try{
            $sql = "SELECT cats.* FROM cartItems, cats
                        WHERE cats.uid = cartItems.cat;";

            $resultSet = $this->pdo->query($sql);
            $results = $resultSet->fetchAll();

            $cats = array();
            foreach ($results as $result) {
                $cats[] = new Cat($result["uid"], $result["name"], $result["age"], $result["description"], $result["whenLastSeen"],
                    $result["race"], $result["furColor"], $result["weight"], $result["image"], $result["imageMimeType"],
                    $result["price"], $result["owner"]);
            }

            return $cats;
        }catch (PDOException $e){
            echo($e->getMessage());
            return null;
        }
    }

    public function update(object $object): bool
    {
        throw new Exception('Not implemented');
    }

    public function delete(int $id): bool
    {
        throw new Exception('Not implemented');
    }
}