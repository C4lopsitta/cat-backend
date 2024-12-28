<?php

class CatDAO extends GenericDAO
{

    public function create(object $object): ?object
    {
        $sql = "INSERT INTO cats(
                     uid, name, age, description, whenLastSeen, whereLastSeen, race, furColor, weight, isStray, image,
                     imageMimeType, price, owner
                 ) VALUES(
                      NULL, :name, :age, :description, :whenLastSeen, :whereLastSeen, :race, :furColor, :weight,
                      :isStray, :image, :imageMimeType, :price, :owner)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name' => $object->getName(),
            ':age' => $object->getAge(),
            ':description' => $object->getDescription(),
            ':whenLastSeen' => $object->getWhenLastSeen(),
            ':whereLastSeen' => $object->getWhereLastSeen(),
            ':race' => $object->getRace(),
            ':furColor' => $object->getFurColor(),
            ':weight' => $object->getWeight(),
            ':isStray' => $object->getIsStray(),
            ':image' => $object->getImage(),
            ':imageMimeType' => $object->getImageMimeType(),
            ':price' => $object->getPrice(),
            ':owner' => $object->getOwner()
        ]);

        $id = $this->pdo->lastInsertId();
        $object->setId($id);

        return $object;
    }

    public function read(int $id): ?object
    {
        // TODO: Implement read() method.
        return null;
    }

    public function readAll(): ?array
    {
        // TODO: Implement readAll() method.
        return null;
    }

    public function readByOwner(int $id): ?object
    {
        // TODO: Implement readByOwner() method
        return null;
    }

    public function update(object $object): bool
    {
        // TODO: Implement update() method.
        return false;
    }

    public function delete(int $id): bool
    {
        // TODO: Implement delete() method.
        return false;
    }
}