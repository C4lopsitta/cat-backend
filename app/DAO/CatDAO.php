<?php

namespace DAO;

use Model\Cat;
use Random\RandomException;
use Utilities\Uid;
use DAO\GenericDAO;

use PDO;

class CatDAO extends GenericDAO
{

    /**
     * @throws RandomException
     */
    public static function create(object $object): ?object
    {
        $sql = "INSERT INTO cats(
                     uid, name, age, description, whenLastSeen, whereLastSeen, race, furColor, weight, isStray, image,
                     imageMimeType, price, owner
                 ) VALUES(
                      :id, :name, :age, :description, :whenLastSeen, :whereLastSeen, :race, :furColor, :weight,
                      :isStray, :image, :imageMimeType, :price, :owner);";

        $uid = Uid::compact(Uid::generate());

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':id' => $uid,
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

        $object->setUid($uid);

        return $object;

    }

    public static function read(int $id): ?object
    {
        $sql = "SELECT * FROM cats WHERE cats.uid = :id;";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_OBJ);
        if ($data) {
            return new Cat($data->uid, $data->name, $data->age, $data->description, $data->whenLastSeen,
                $data->whereLastSeen, $data->race, $data->furColor, $data->weight, $data->image, $data->imageMimeType,
                $data->price, $data->owner);
        }

        return null;
    }

    public static function readAll(): ?array
    {
        $sql = "SELECT * FROM cats;";

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

    public static function readByOwner(int $idOwner): ?array
    {
        $sql = "SELECT * FROM cats WHERE cats.owner = :idOwner;";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':idOwner' => $idOwner]);
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        $cats = array();
        foreach ($results as $result) {
            $cats[] = new Cat($result->uid, $result->name, $result->age, $result->description, $result->whenLastSeen,
                $result->whereLastSeen, $result->race, $result->furColor, $result->weight, $result->image, $result->imageMimeType,
                $result->price, $result->owner);
        }

        return $cats;
    }

    public static function update(object $object): bool
    {
        $sql = "UPDATE cats SET
                name = :name,
                age = :age,
                description = :description,
                whenLastSeen = :whenLastSeen,
                whereLastSeen = :whereLastSeen,
                race = :race,
                furColor = :furColor,
                weight = :weight,
                isStray = :isStray,
                image = :image,
                imageMimeType = :imageMimeType,
                price = :price,
                owner = :owner
                WHERE cats.uid = :id;
        ";
        $stmt = self::$pdo->prepare($sql);

        return $stmt->execute([
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
            ':owner' => $object->getOwner(),
            ':id' => $object->getUid()
        ]);
    }

    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM cats WHERE cats.uid = :id;";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}