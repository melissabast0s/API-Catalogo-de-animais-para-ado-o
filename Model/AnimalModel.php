<?php

namespace Model;

use PDO;

class AnimalModel
{
    private PDO $conn;
    private string $table = "animais";

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    // 1. Resolve os avisos de readAll e readById
    public function readAll(): array
    {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById(string $id): ?array
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // 2. Resolve o aviso: "Too many arguments to function create(). 6 provided"
    public function create(string $nome, string $especie, string $raca, float $idade, string $status, ?string $foto): bool
    {
        $query = "INSERT INTO " . $this->table . " (nome, especie, raca, idade, status, foto) 
                  VALUES (:nome, :especie, :raca, :idade, :status, :foto)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':especie', $especie);
        $stmt->bindValue(':raca', $raca);
        $stmt->bindValue(':idade', $idade);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':foto', $foto);

        return $stmt->execute();
    }

    // 3. Resolve o aviso: "Too many arguments to function update(). 7 provided"
    public function update(string $id, string $nome, string $especie, string $raca, float $idade, string $status, ?string $foto): bool
    {
        $query = "UPDATE " . $this->table . " 
                  SET nome = :nome, especie = :especie, raca = :raca, idade = :idade, status = :status, foto = :foto 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':especie', $especie);
        $stmt->bindValue(':raca', $raca);
        $stmt->bindValue(':idade', $idade);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':foto', $foto);

        return $stmt->execute();
    }

    public function delete(string $id): bool
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}