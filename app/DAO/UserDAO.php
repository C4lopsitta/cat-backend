<?php

namespace DAO;

use DAO\GenericDAO;
use Utilities\Uid;
use Model\User;

use PDO;
use Exception;

class UserDAO extends GenericDAO
{

    public function create(object $object): ?object
    {
        try {
            $sql = "INSERT INTO users(uid, username, email, passwordHash) 
                        VALUES(:id, :username, :email, :passwordHash);";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => UUID . genUuid(),
                ':username' => $object->username,
                ':email' => $object->email,
                ':passwordHash' => $object->passwordHash
            ]);

            $id = $this->pdo->lastInsertId();
            $object->setUid($id);

            return $object;
        } catch (PDOException $e) {
            echo($e->getMessage());
            return null;
        }
    }

    public function read(int $id): ?object
    {
        try {
            $sql = "SELECT * FROM users WHERE users.uid = :id;";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            $data = $stmt->fetch(PDO::FETCH_OBJ);
            if ($data) {
                return new User($data->uid, $data->username, $data->email, $data->passwordHash);
            }

            return null;
        } catch (PDOException $e) {
            echo($e->getMessage());
            return null;
        }
    }

    public function readAll(): ?array
    {
        try {
            $sql = "SELECT * FROM users;";

            $resultSet = $this->pdo->query($sql);
            $results = $resultSet->fetchAll();

            $cats = array();
            foreach ($results as $result) {
                $cats[] = new User($result["uid"], $result["username"], $result["email"], $result["passwordHash"]);
            }

            return $cats;
        } catch (PDOException $e) {
            echo($e->getMessage());
            return null;
        }
    }

    public function update(object $object): bool
    {
        try {
            $sql = "UPDATE users SET
                username = :username,
                email = :email,
                passwordHash = :passwordHash
                WHERE users.uid = :id;
        ";
            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute([
                ':username' => $object->username,
                ':email' => $object->email,
                ':passwordHash' => $object->passwordHash,
                ':id' => $object->id
            ]);
        } catch (PDOException $e) {
            echo($e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM users WHERE users.uid = :id;";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            echo($e->getMessage());
            return false;
        }
    }
}