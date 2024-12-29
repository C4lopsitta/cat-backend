<?php

include_once 'DAO/GenericDAO.php';

class CatDAO extends GenericDAO
{

    public function create(object $object): ?object
    {
        try{
            // uid NULL to replace with a string (in some mysterious way that I don't know now)
            $sql = "INSERT INTO cats(
                     uid, name, age, description, whenLastSeen, whereLastSeen, race, furColor, weight, isStray, image,
                     imageMimeType, price, owner
                 ) VALUES(
                      NULL, :name, :age, :description, :whenLastSeen, :whereLastSeen, :race, :furColor, :weight,
                      :isStray, :image, :imageMimeType, :price, :owner);";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $object->getId(),
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
        }catch (PDOException $e){
            echo($e->getMessage());
            return null;
        }

    }

    public function read(int $id): ?object
    {
        try{
            $sql = "SELECT * FROM cats WHERE cats.uid = :id;";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            $data = $stmt->fetch(PDO::FETCH_OBJ);
            if($data){
                return new Cat($data->uid, $data->name, $data->age, $data->description, $data->whenLastSeen,
                    $data->whereLastSeen, $data->race, $data->furColor, $data->weight, $data->image, $data->imageMimeType,
                    $data->price, $data->owner);
            }

            return null;
        }catch (PDOException $e){
            echo($e->getMessage());
            return null;
        }
    }

    public function readAll(): ?array
    {
        try{
            $sql = "SELECT * FROM cats;";

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