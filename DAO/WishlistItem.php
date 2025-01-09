<?php

class WishlistItem extends GenericDAO
{

    /**
     * @throws Exception
     */
    public function create(object $object): ?object
    {
        throw new Exception('Not implemented');
    }

    /**
     * @throws Exception
     */
    public function read(int $id): ?object
    {
        throw new Exception('Not implemented');
    }

    /**
     * @throws Exception
     */
    public function readAll(): ?array
    {
        throw new Exception('Not implemented');
    }

    /**
     * @throws Exception
     */
    public function update(object $object): bool
    {
        throw new Exception('Not implemented');
    }

    public function delete(int $id): bool
    {
        try{
            $sql = "DELETE FROM wishListItems 
                        WHERE wishListItems.uid = :id;";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);

        }catch (PDOException $e){
            echo($e->getMessage());
            return false;
        }
    }
}