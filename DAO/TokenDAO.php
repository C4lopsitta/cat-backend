<?php

include_once 'DAO/GenericDAO.php';
include_once 'Model/Token.php';

class TokenDAO extends GenericDAO
{

    public function create(object $object): ?object
    {
        try{
            $sql = "INSERT INTO tokens(token, user, expirationDate) 
                        VALUES(:token, :user, :expirationDate);";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':token' => $object->getToken(),
                ':user' => $object->getUser(),
                ':expirationDate' => $object->getExpirationDate()
            ]);

            return $object;
        }catch (PDOException $e){
            echo($e->getMessage());
            return null;
        }
    }

    public function read(int $id): ?object
    {
        try{
            $sql = "SELECT * FROM tokens WHERE tokens.token = :token;";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':token' => $id]);

            $data = $stmt->fetch(PDO::FETCH_OBJ);
            if($data){
                return new Token($data->token, $data->user, $data->expirationDate);
            }

            return null;
        }catch (PDOException $e){
            echo($e->getMessage());
            return null;
        }
    }

    public function readByUser(int $userId): ?array
    {
        try{
            $sql = "SELECT * FROM tokens WHERE tokens.user = :user;";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':user' => $userId]);

            $results = $stmt->fetchAll(PDO::FETCH_OBJ);

            $data = array();
            foreach($results as $result){
                $data[] = new Token($result['token'], $result['user'], $data['expirationDate']);
            }

            return null;
        }catch (PDOException $e){
            echo($e->getMessage());
            return null;
        }
    }

    public function readAll(): ?array
    {
        return null;
    }

    public function update(object $object): bool
    {
        return false;
    }

    public function delete(int $id): bool
    {
        return false;
    }
}