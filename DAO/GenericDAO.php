<?php

abstract class GenericDAO {
	protected ?PDO $pdo = null;

	public function __construct() { }
	public function connect(string $conn_string): ?bool {
		if ($this->pdo != null)	return false;
		$this->pdo = new PDO($conn_string);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return true;
	}	
	public function disconnect() { $this->pdo = null; }
	
	abstract public function create(object $object): ?int;
	abstract public function read(int $id): ?object;
	abstract public function readAll(): ?array;
	abstract public function update(object $object): bool;
	abstract public function delete(int $id): bool;
}
