<?php
include_once("GenericDAO.php");
include_once("Entity.php");

class EntityDAO extends GenericDAO {

	public function __construct() { }

    public function create(Object $object): int
    {
        $sql = "INSERT INTO entities (descr, value) VALUES (:descr, :value)";
        $stmt = $this->pdo->prepare($sql);
        //$entity = Entity::from_json($object);
        $stmt->execute(array(
            ':descr' => $object->descr,
            ':value' => $object->value
        ));
        $id = $this->pdo->lastInsertId();
        if ($id != -1)
			$object->id = $this->pdo->lastInsertId();
			 
        return (int) $this->pdo->lastInsertId();
    }

    // Read by ID
    public function read(int $id): ?Entity
    {
        $sql = "SELECT * FROM entities WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_OBJ);
        if ($data) {
            return new Entity($data->id, $data->descr, $data->value);
        }
        return null;
    }

    // Update
    public function update(Object $object): bool
    {
        $sql = "UPDATE entities SET descr = :descr, value = :value WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $object->id,
            ':descr' => $object->descr,
            ':value' => $object->value
        ]);
    }

    // Delete by ID
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM entities WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Read all
    public function readAll(): array
    {
        $sql = "SELECT id, descr, value FROM entities";
        $rows = $this->pdo->query($sql)->fetchAll();
        $entities = [];
        foreach ($rows as $row) {
        	$entities[] = new Entity($row["id"], $row ["descr"], $row["value"]);
        }
        return $entities;
    }
}
?>
