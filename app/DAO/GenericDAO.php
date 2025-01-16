<?php

namespace DAO;

use PDO;

abstract class GenericDAO {
  protected static ?PDO $pdo = null;

  public static function connect(): bool {
    if (self::$pdo != null) return false;
    $connectionString = "mysql:host=db;port=3306;dbname=" . getenv("DB_NAME") . ";charset=utf8mb4";

    self::$pdo = new PDO($connectionString, getenv('DB_USER'), getenv('DB_PASSWD'));
    self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return true;
  }

  public static function disconnect() {
    self::$pdo = null;
  }

  abstract static public function create(object $object): ?object;

  abstract static public function read(int $id);

  abstract static public function readAll(): ?array;

  abstract static public function update(object $object): bool;

  abstract static public function delete(int $id): bool;
}

?>
