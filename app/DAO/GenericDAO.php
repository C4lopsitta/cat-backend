<?php

namespace DAO;

use PDO;

abstract class GenericDAO {
  protected static ?PDO $pdo = null;

  public function __construct() {}

  public function connect(): bool {
    if ($this->pdo != null) return false;
    $connectionString = "mysql:host=db;port=3306;dbname=" . getenv("DB_NAME") . ";charset=utf8mb4";

    $this->pdo = new PDO($connectionString, getenv('DB_USER'), getenv('DB_PASSWD'));
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return true;
  }

  public function disconnect() {
    $this->pdo = null;
  }

  abstract public function create(object $object): ?object;

  abstract public function read(int $id);

  abstract public function readAll(): ?array;

  abstract public function update(object $object): bool;

  abstract public function delete(int $id): bool;
}

?>
