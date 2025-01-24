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

/**
 * User Data Access Object (DAO) class for handling CRUD operations and user-specific queries.
 * This class interacts with the database to manage user entities and associated functionality.
 */

namespace DAO;

use DAO\GenericDAO;
use Random\RandomException;
use Utilities\Uid;
use Model\User;
use PDO;

/**
 * Data Access Object (DAO) for managing user records in a persistent data storage.
 * This class provides CRUD operations and additional utility functions to interact
 * with user-related data in the database.
 */
class UserDAO extends GenericDAO {
    private const SQL_CREATE_USER = "INSERT INTO users 
                        VALUES(:uid, :username, :email, null, null, :description, :pronouns, :passwordHash, false)";
    private const SQL_READ_USER = "SELECT * FROM users WHERE users.uid LIKE :uid;";
    private const SQL_CHECK_ACCOUNT_CONFIRMED = "SELECT * FROM users WHERE users.uid = :uid AND isAccountConfirmed = true;";
    private const SQL_FETCH_UID_FROM_EMAIL = "SELECT uid FROM users WHERE email= :email;";
    private const SQL_CHECK_USER_EXISTS = "SELECT * FROM users WHERE email LIKE :email;";
    private const SQL_READ_ALL_CONFIRMED_USERS = "SELECT * FROM users WHERE isAccountConfirmed = true;";
    private const SQL_UPDATE_USER = "UPDATE users SET
                username = :username,
                description = :description,
                pronouns = :pronouns,
                image = :image,
                imageMimeType = :imageMimeType,
                isAccountConfirmed = :isAccountConfirmed
                WHERE users.uid LIKE :id;";
    private const SQL_DELETE_USER = "DELETE FROM users WHERE users.uid = :id;";

    /**
     * Creates a new user object in the database with the given object's properties.
     *
     * @param object $object An object representing the user to be created, containing the necessary attributes such as username, email, description, pronouns, and password hash.
     *
     * @return object|null The same user object with its UID set after creation, or null if the operation fails.
     */
    public static function create(object $object): ?object {
        $uid = Uid::compact(Uid::generate());

        self::prepareAndExecute(self::SQL_CREATE_USER, [
           'uid' => $uid,
           'username' => $object->getUsername(),
           'email' => $object->getEmail(),
           'description' => $object->getDescription(),
           'pronouns' => $object->getPronouns(),
           'passwordHash' => $object->getPasswordHash(),
        ]);

        $object->setUid(Uid::format($uid));
        return $object;
    }

    /**
     * Reads a user record from the database based on the provided identifier.
     *
     * @param string $id The unique identifier of the user to be read.
     * @return object|null The user object if found, or null if no matching record exists.
     */
    public static function read(string $id): ?object {
        $uid = Uid::compact($id);

        $stmt = self::prepareAndExecute(self::SQL_READ_USER, ['uid' => $uid]);
        $userRecord = $stmt->fetch(PDO::FETCH_OBJ);

        return $userRecord ? self::mapUser($userRecord) : null;
    }

    /**
     * Checks whether a user's account is confirmed based on the provided user ID.
     *
     * @param string $uid The unique identifier of the user to check.
     * @return bool True if the user's account is confirmed, false otherwise.
     */
    public static function isUserAccountConfirmed(string $uid): bool {
        $stmt = self::prepareAndExecute(self::SQL_CHECK_ACCOUNT_CONFIRMED, ['uid' => $uid]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Fetches the user UID associated with the given email address.
     *
     * @param string $email The email address of the user.
     * @return string|null The compact UID of the user if found, or null if no matching record exists.
     */
    public static function fetchUserUidFromEmail(string $email): ?string {
        $stmt = self::prepareAndExecute(self::SQL_FETCH_UID_FROM_EMAIL, ['email' => $email]);
        $userRecord = $stmt->fetch(PDO::FETCH_OBJ);

        return $userRecord ? Uid::compact($userRecord->uid) : null;
    }

    /**
     * Checks if a user exists in the database based on their email.
     *
     * @param string $email The email address of the user to check.
     * @return bool Returns true if the user exists, false otherwise.
     */
    public static function doesUserExist(string $email): bool {
        $stmt = self::prepareAndExecute(self::SQL_CHECK_USER_EXISTS, ['email' => $email]);
        return (bool)$stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Retrieves all confirmed users from the database, processes the results, and maps them to the desired format.
     *
     * @return array|null An array of mapped user objects or null if no data is available.
     */
    public static function readAll(): ?array {
        $resultSet = self::$pdo->query(self::SQL_READ_ALL_CONFIRMED_USERS);
        $results = $resultSet->fetchAll(PDO::FETCH_OBJ);

        return array_map([self::class, 'mapUser'], $results);
    }

    /**
     * Updates an existing record in the database with the provided object data.
     *
     * @param object $object The object containing the updated data to be persisted.
     * @return bool Returns true if the update operation was successful, otherwise false.
     */
    public static function update(object $object): bool {
        return self::prepareAndExecute(self::SQL_UPDATE_USER, [
              ':username' => $object->getUsername(),
              ':description' => $object->getDescription(),
              ':pronouns' => $object->getPronouns(),
              ':image' => $object->getImage(),
              ':imageMimeType' => $object->getImageMimeType(),
              ':isAccountConfirmed' => $object->isAccountConfirmed(),
              ':id' => $object->getUid(),
           ])->rowCount() > 0;
    }

    /**
     * Deletes a user identified by the given ID.
     *
     * @param string $id The unique identifier of the user to be deleted.
     * @return bool Returns true if the user was successfully deleted, otherwise false.
     */
    public static function delete(string $id): bool {
        $uid = Uid::compact($id);
        return self::prepareAndExecute(self::SQL_DELETE_USER, [':id' => $uid])->rowCount() > 0;
    }

    /**
     * Maps a database record object to a User instance.
     *
     * @param object $record The database record containing user data.
     * @return User A User object populated with data from the provided record.
     */
    private static function mapUser(object $record): User {
        return new User(
           $record->username,
           $record->uid,
           $record->email,
           $record->image,
           $record->imageMimeType,
           $record->description,
           $record->pronouns,
           $record->passwordHash,
           $record->isAccountConfirmed
        );
    }

    /**
     * Prepares and executes an SQL statement with the provided parameters.
     *
     * @param string $sql The SQL query to be prepared and executed.
     * @param array $params An associative array of parameters to bind to the SQL query.
     * @return PDOStatement The prepared and executed PDO statement.
     */
    private static function prepareAndExecute(string $sql, array $params): PDOStatement {
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}