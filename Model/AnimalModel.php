<?php
namespace Model;

use Config\Connection;
use PDO;

class AnimalModel {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getConnection();
    }

    public function getAll($status = null) {
        if ($status) {
            $stmt = $this->conn->prepare("SELECT * FROM animais WHERE status = :status ORDER BY id DESC");
            $stmt->bindValue(':status', $status);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $stmt = $this->conn->query("SELECT * FROM animais ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM animais WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO animais (nome, especie, raca, idade, status, foto) 
                VALUES (:nome, :especie, :raca, :idade, :status, :foto)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':especie', $data['especie']);
        $stmt->bindValue(':raca', $data['raca']);
        $stmt->bindValue(':idade', $data['idade']);
        $stmt->bindValue(':status', $data['status'] ?? 'Disponível');
        $stmt->bindValue(':foto', $data['foto'] ?? null);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
        $sql = "UPDATE animais 
                SET nome = :nome, especie = :especie, raca = :raca, idade = :idade, status = :status, foto = :foto 
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':especie', $data['especie']);
        $stmt->bindValue(':raca', $data['raca']);
        $stmt->bindValue(':idade', $data['idade']);
        $stmt->bindValue(':status', $data['status']);
        $stmt->bindValue(':foto', $data['foto'] ?? null);

        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM animais WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}