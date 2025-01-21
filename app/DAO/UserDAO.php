<?php /** @noinspection ALL */
/** @noinspection ALL */
/** @noinspection ALL */

/*
 * Copyright (c) 2025.
 *
 * Code is licensed under GNU GPLv3 License and available in the Copying file.
 * Code was written by:
 * - Simone Robaldo ( simone.robaldo at itiscuneo.eu )
 * - Giulia Vadelli ( giulia.vadelli at itiscuneo.eu )
 * - Daniele Torchio ( daniele.torchio at itiscuneo.eu )
 */

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
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
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
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
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
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
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
}