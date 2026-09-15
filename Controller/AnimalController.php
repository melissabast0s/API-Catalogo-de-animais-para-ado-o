<?php
namespace Controller;

use Model\AnimalModel;

class AnimalController {
    private $animalModel;

    public function __construct(AnimalModel $animalModel) {
        $this->animalModel = $animalModel;
    }

    public function ProcessRequest($method, $id) {
       
        $action = strtolower($method);

        if (method_exists($this, $action)) {
            $this->$action($id);
        } else {
            http_response_code(405);
            echo json_encode(["status" => false, "message" => "Método não permitido."]);
        }
    }

    private function get($id = null) {
        if ($id) {
            $animal = $this->animalModel->getById($id);
            if ($animal) {
                $this->response(true, "Animal encontrado.", $animal, 200);
            } else {
                $this->response(false, "Animal não encontrado.", null, 404);
            }
        } else {
            $status = $_GET['status'] ?? null;
            $animais = $this->animalModel->getAll($status);
            $this->response(true, "Lista de animais recuperada com sucesso.", $animais, 200);
        }
    }

    private function post($id = null) {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['nome']) || empty($data['especie']) || empty($data['raca']) || !isset($data['idade'])) {
            $this->response(false, "Campos obrigatórios ausentes: nome, especie, raca, idade.", null, 400);
            return;
        }

        $id = $this->animalModel->create($data);
        if ($id) {
            $this->response(true, "Animal cadastrado com sucesso!", ["id" => $id], 201);
        } else {
            $this->response(false, "Erro ao cadastrar o animal.", null, 500);
        }
    }

    private function put($id = null) {
        if (!$id) {
            $this->response(false, "ID do animal não fornecido.", null, 400);
            return;
        }

        if (!$this->animalModel->getById($id)) {
            $this->response(false, "Animal não encontrado.", null, 404);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['nome']) || empty($data['especie']) || empty($data['raca']) || !isset($data['idade']) || empty($data['status'])) {
            $this->response(false, "Dados insuficientes para atualização.", null, 400);
            return;
        }

        if ($this->animalModel->update($id, $data)) {
            $this->response(true, "Dados do animal atualizados com sucesso.", null, 200);
        } else {
            $this->response(false, "Erro ao atualizar dados.", null, 500);
        }
    }

    private function delete($id = null) {
        if (!$id) {
            $this->response(false, "ID do animal não fornecido.", null, 400);
            return;
        }

        if ($this->animalModel->delete($id)) {
            $this->response(true, "Animal removido do sistema com sucesso.", null, 200);
        } else {
            $this->response(false, "Animal não encontrado para remoção.", null, 404);
        }
    }

    private function response($status, $message, $data = null, $code = 200) {
        http_response_code($code);
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}