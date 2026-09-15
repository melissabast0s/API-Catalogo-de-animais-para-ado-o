<?php

namespace Controller;

use Model\AnimalModel;

class AnimalController
{
    private AnimalModel $model;

    public function __construct(AnimalModel $model)
    {
        $this->model = $model;
    }

    public function ProcessRequest(string $method, ?string $id = null): void
    {
        $routes = [
            'GET'    => $id ? 'getById' : 'getAll',
            'POST'   => 'create',
            'PUT'    => 'update',
            'DELETE' => 'delete'
        ];

        $action = $routes[$method] ?? null;

        if (!$action || !method_exists($this, $action)) {
            http_response_code(405);
            echo json_encode(["status" => false, "message" => "Método não permitido."]);
            return;
        }

        $this->$action($id);
    }

    private function getAll(?string $id): void
    {
        $data = $this->model->readAll();


        $data = array_map(function ($item) {
            $item['foto'] = $this->formatPhotoUrl($item['foto']);
            return $item;
        }, $data);

        http_response_code(200);
        echo json_encode([
            "status"  => true,
            "message" => "Lista de animais recuperada com sucesso.",
            "data"    => $data
        ]);
    }

    private function getById(?string $id): void
    {
        $data = $this->model->readById($id);

        if (!$data) {
            http_response_code(404);
            echo json_encode(["status" => false, "message" => "Animal não encontrado.", "data" => null]);
            return;
        }

        $data['foto'] = $this->formatPhotoUrl($data['foto']);

        http_response_code(200);
        echo json_encode(["status" => true, "data" => $data]);
    }

    private function create(?string $id): void
    {
        $nome    = $_POST['nome'] ?? null;
        $especie = $_POST['especie'] ?? null;
        $raca    = $_POST['raca'] ?? null;
        $idade   = $_POST['idade'] ?? null;
        $status  = $_POST['status'] ?? 'Disponível';
        $foto    = $this->handleFileUpload();

        if (!$nome || !$especie || !$raca || !$idade) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Preencha os campos obrigatórios."]);
            return;
        }

        $success = $this->model->create($nome, $especie, $raca, $idade, $status, $foto);

        if ($success) {
            http_response_code(201);
            echo json_encode(["status" => true, "message" => "Animal cadastrado com sucesso."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => false, "message" => "Erro ao cadastrar animal."]);
        }
    }

    private function update(?string $id): void
    {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "ID é obrigatório para atualização."]);
            return;
        }

        $input = json_decode(file_get_contents("php://input"), true) ?? $_POST;

        $nome    = $input['nome'] ?? null;
        $especie = $input['especie'] ?? null;
        $raca    = $input['raca'] ?? null;
        $idade   = $input['idade'] ?? null;
        $status  = $input['status'] ?? 'Disponível';
        $foto    = $this->handleFileUpload() ?? ($input['foto'] ?? null);

        $success = $this->model->update($id, $nome, $especie, $raca, $idade, $status, $foto);

        if ($success) {
            http_response_code(200);
            echo json_encode(["status" => true, "message" => "Animal atualizado com sucesso."]);
        } else {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Falha na atualização."]);
        }
    }

    private function delete(?string $id): void
    {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "ID não informado."]);
            return;
        }

        $success = $this->model->delete($id);

        if ($success) {
            http_response_code(200);
            echo json_encode(["status" => true, "message" => "Animal removido com sucesso."]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => false, "message" => "Animal não encontrado para remoção.", "data" => null]);
        }
    }

    private function handleFileUpload(): ?string
    {
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $extensoesValidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extensao, $extensoesValidas)) {
            return null;
        }

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $novoNome = uniqid("pet_") . "." . $extensao;
        $destino = $uploadDir . $novoNome;

        return move_uploaded_file($_FILES['foto']['tmp_name'], $destino) ? $novoNome : null;
    }

    private function formatPhotoUrl(?string $fotoName): ?string
    {
        if (!$fotoName) {
            return null;
        }
        return "http://localhost:8000/uploads/" . $fotoName;
    }
}