<?php

require_once __DIR__ . '/../Model/AnimalModel.php';

class AnimalController {
    private $model;

    public function __construct() {
        $this->model = new AnimalModel();
    }

    public function get($id = null) {
        if ($id) {
            $animal = $this->model->getById($id);
            if ($animal) {
                $this->response(true, "Animal encontrado.", $animal, 200);
            } else {
                $this->response(false, "Animal não encontrado.", null, 404);
            }
        } else {
            $status = $_GET['status'] ?? null;
            $animais = $this->model->getAll($status);
            $this->response(true, "Lista de animais recuperada com sucesso.", $animais, 200);
        }
    }

    public function post() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['nome']) || empty($data['especie']) || empty($data['raca']) || !isset($data['idade'])) {
            $this->response(false, "Campos obrigatórios ausentes: nome, especie, raca, idade.", null, 400);
            return;
        }

        $id = $this->model->create($data);
        if ($id) {
            $this->response(true, "Animal cadastrado com sucesso!", ["id" => $id], 201);
        } else {
            $this->response(false, "Erro ao cadastrar o animal.", null, 500);
        }
    }

    public function put($id) {
        if (!$id) {
            $this->response(false, "ID do animal não fornecido.", null, 400);
            return;
        }

        if (!$this->model->getById($id)) {
            $this->response(false, "Animal não encontrado.", null, 404);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['nome']) || empty($data['especie']) || empty($data['raca']) || !isset($data['idade']) || empty($data['status'])) {
            $this->response(false, "Dados insuficientes para atualização.", null, 400);
            return;
        }

        if ($this->model->update($id, $data)) {
            $this->response(true, "Dados do animal atualizados com sucesso.", null, 200);
        } else {
            $this->response(false, "Erro ao atualizar dados.", null, 500);
        }
    }

    public function delete($id) {
        if (!$id) {
            $this->response(false, "ID do animal não fornecido.", null, 400);
            return;
        }

        if ($this->model->delete($id)) {
            $this->response(true, "Animal removido do sistema com sucesso.", null, 200);
        } else {
            $this->response(false, "Animal não encontrado para remoção.", null, 404);
        }
    }

    private function response($status, $message, $data = null, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}