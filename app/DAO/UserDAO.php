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
    public static function create(object $object): ?object {
        $uid = Uid::compact(Uid::generate());

        $sql = "INSERT INTO users 
                        VALUES(:uid, '{$object->getUsername()}', '{$object->getEmail()}', null, null, '{$object->getDescription()}', '{$object->getPronouns()}', '{$object->getPasswordHash()}', false)";


        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['uid' => $uid]);

        $object->setUid(Uid::format($uid));

        return $object;
    }

    public static function read(string $id): ?object {
        $id = Uid::compact($id);
        $sql = "SELECT * FROM users WHERE users.uid LIKE '{$id}';";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_OBJ);
        if ($data) {
            return new User($data->username, $data->uid, $data->email, $data->image, $data->imageMimeType,
                $data->description, $data->pronouns, $data->passwordHash, $data->isAccountConfirmed);
        }

        return null;
    }

    public static function fetchUserUidFromEmail(string $email): ?string {
        $sql = "SELECT uid FROM users WHERE email='{$email}';";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_OBJ);

        if ($data) {
            return Uid::compact($data->uid);
        }
        return null;
    }



    public static function doesUserExist(string $email): bool {
        $sql = "SELECT * FROM users WHERE email LIKE '{$email}';";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_OBJ);
        return (bool)$data;
    }

    public static function readAll(): ?array {
        $sql = "SELECT * FROM users;";

        $resultSet = self::$pdo->query($sql);
        $results = $resultSet->fetchAll();

        $cats = array();
        foreach ($results as $result) {
            $cats[] = new User($result["username"], $result["uid"], $result["email"], $result["image"],
                $result["imageMimeType"], $result["description"], $result["pronouns"], $result["passwordHash"],
                $result["isAccountConfirmed"]);
        }

        return $cats;
    }

    public static function update(object $object): bool {
        $sql = "UPDATE users SET
                username = :username,
                description = :description,
                pronouns = :pronouns,
                image = :image,
                imageMimeType = :imageMimeType,
                isAccountConfirmed = :isAccountConfirmed
                WHERE users.uid LIKE :id;
        ";
        $stmt = self::$pdo->prepare($sql);

        return $stmt->execute([
            ':username' => $object->getUsername(),
            ':description' => $object->getDescription(),
            ':pronouns' => $object->getPronouns(),
            ':image' => $object->getImage(),
            ':imageMimeType' => $object->getImageMimeType(),
            ':isAccountConfirmed' => $object->isAccountConfirmed(),
            ':id' => $object->getUid()
        ]);
    }

    public static function delete(string $id): bool {
        $sql = "DELETE FROM users WHERE users.uid = :id;";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public static function storeUserSecret(string $userUID, string $secret): bool {
        $user = self::read($userUID);
        $sql = "UPDATE users SET
            key2FA = :key2FA
            WHERE users.uid LIKE :id;
    ";
        $stmt = self::$pdo->prepare($sql);

        return $stmt->execute([
            ':key2FA' => $user->getKey2FA(),
            ':id' => $user->getUid()
        ]);
    }

    public static function readTFA(string $uid): ?string {
        $sql = "SELECT key2FA FROM users WHERE uid LIKE :id";
        $stmt = self::$pdo->prepare($sql);

        $stmt->execute([
            ':id' => $uid
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['key2FA'] : null;
    }

}