<?php

namespace DAO;

use DAO\GenericDAO;
use Random\RandomException;
use Utilities\Uid;
use Model\User;

use PDO;

class UserDAO extends GenericDAO
{

    /**
     * @throws RandomException
     */
    public static function create(object $object): ?object
    {
        $sql = "INSERT INTO users(uid, username, email, passwordHash) 
                        VALUES(:id, :username, :email, :passwordHash);";

        $uid = Uid::compact(Uid::generate());

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':id' => $uid,
            ':username' => $object->username,
            ':email' => $object->email,
            ':passwordHash' => $object->passwordHash
        ]);

        $object->setUid($uid);

        return $object;
    }

    public static function read(int $id): ?object
    {
        $sql = "SELECT * FROM users WHERE users.uid = :id;";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_OBJ);
        if ($data) {
            return new User($data->uid, $data->username, $data->email, $data->image, $data->imageMimeType,
                $data->description, $data->pronouns, $data->passwordHash, $data->isAccountConfirmed);
        }

        return null;
    }

    public static function readAll(): ?array
    {
        $sql = "SELECT * FROM users;";

        $resultSet = self::$pdo->query($sql);
        $results = $resultSet->fetchAll();

        $cats = array();
        foreach ($results as $result) {
            $cats[] = new User($result["uid"], $result["username"], $result["email"], $result["image"],
                $result["imageMimeType"], $result["description"], $result["pronouns"], $result["passwordHash"],
                $result["isAccountConfirmed"]);
        }

        return $cats;
    }

    public static function update(object $object): bool
    {
        $sql = "UPDATE users SET
                username = :username,
                email = :email,
                passwordHash = :passwordHash
                WHERE users.uid = :id;
        ";
        $stmt = self::$pdo->prepare($sql);

        return $stmt->execute([
            ':username' => $object->username,
            ':email' => $object->email,
            ':passwordHash' => $object->passwordHash,
            ':id' => $object->id
        ]);
    }

    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM users WHERE users.uid = :id;";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}