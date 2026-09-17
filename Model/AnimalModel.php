<?php
namespace Model;

use Model\Connection;
use PDO;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Animais",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "nome", type: "string"),
        new OA\Property(property: "especie", type: "string"),
        new OA\Property(property: "raca", type: "string"),
        new OA\Property(property: "idade", type: "integer"),
        new OA\Property(property: "status", type: "string"),
        new OA\Property(property: "foto", type: "string", nullable: true)
    ]
)]

#[OA\Schema(
    schema: "AnimalInput",
    required: ["nome", "especie", "raca", "idade"],
    properties: [
        new OA\Property(property: "nome", type: "string", example: "Thor"),
        new OA\Property(property: "especie", type: "string", example: "Cão"),
        new OA\Property(property: "raca", type: "string", example: "Vira-lata"),
        new OA\Property(property: "idade", type: "integer", example: 3),
        new OA\Property(property: "status", type: "string", example: "Disponível"),
        new OA\Property(property: "foto", type: "string", example: "thor.jpg")
    ]
)]

#[OA\Schema(
    schema: "AnimalUpdateInput",
    properties: [
        new OA\Property(property: "nome", type: "string", example: "Thor"),
        new OA\Property(property: "especie", type: "string", example: "Cão"),
        new OA\Property(property: "raca", type: "string", example: "Vira-lata"),
        new OA\Property(property: "idade", type: "integer", example: 4),
        new OA\Property(property: "status", type: "string", example: "Adotado"),
        new OA\Property(property: "foto", type: "string", example: "thor_novo.jpg")
    ]
)]
class AnimalModel {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getConnection();
    }

    public function getAll($status = null) {
        if ($status) {
            $stmt = $this->conn->prepare("SELECT * FROM animais WHERE status = :status ORDER BY id DESC");
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
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
        $stmt->bindValue(':nome', $data['nome'], PDO::PARAM_STR);
        $stmt->bindValue(':especie', $data['especie'], PDO::PARAM_STR);
        $stmt->bindValue(':raca', $data['raca'], PDO::PARAM_STR);
        $stmt->bindValue(':idade', (int)$data['idade'], PDO::PARAM_INT);
        $stmt->bindValue(':status', $data['status'] ?? 'Disponível', PDO::PARAM_STR);
        
        if (isset($data['foto']) && $data['foto'] !== null) {
            $stmt->bindValue(':foto', $data['foto'], PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':foto', null, PDO::PARAM_NULL);
        }

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
        $stmt->bindValue(':nome', $data['nome'], PDO::PARAM_STR);
        $stmt->bindValue(':especie', $data['especie'], PDO::PARAM_STR);
        $stmt->bindValue(':raca', $data['raca'], PDO::PARAM_STR);
        $stmt->bindValue(':idade', (int)$data['idade'], PDO::PARAM_INT);
        $stmt->bindValue(':status', $data['status'], PDO::PARAM_STR);
        
        if (isset($data['foto']) && $data['foto'] !== null) {
            $stmt->bindValue(':foto', $data['foto'], PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':foto', null, PDO::PARAM_NULL);
        }

        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM animais WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}