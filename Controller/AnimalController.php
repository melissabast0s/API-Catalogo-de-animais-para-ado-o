<?php
namespace Controller;

use Model\AnimalModel;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Catálogo de Animais para Adoção",
    version: "1.0.0",
    description: "API REST para gerenciamento de animais disponíveis para adoção."
)]
class AnimalController {
    private AnimalModel $animalModel;

    public function __construct(AnimalModel $animalModel) {
        $this->animalModel = $animalModel;
    }

    public function ProcessRequest(string $method, ?int $id = null): void {
        $action = strtolower($method);

        if (method_exists($this, $action)) {
            $this->$action($id);
        } else {
            http_response_code(405);
            echo json_encode(["status" => false, "message" => "Método não permitido."]);
        }
    }

    #[OA\Get(
        path: "/animais",
        summary: "Lista todos os animais ou filtra por ID/Status",
        tags: ["Animais"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Requisição concluída com sucesso",
                content: new OA\JsonContent(ref: "#/components/schemas/Animais")
            ),
            new OA\Response(
                response: 404,
                description: "Animal não encontrado",
                content: new OA\JsonContent(ref: "#/components/schemas/Animais")
            ),
            new OA\Response(response: 500, description: "Erro interno do servidor")
        ]
    )]
    #[OA\Get(
        path: "/animais/{id}",
        summary: "Obtendo informações de um animal específico",
        tags: ["Animais"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Requisição realizada com sucesso",
                content: new OA\JsonContent(ref: "#/components/schemas/Animais")
            ),
            new OA\Response(
                response: 404,
                description: "Animal não encontrado",
                content: new OA\JsonContent(ref: "#/components/schemas/Animais")
            ),
            new OA\Response(response: 500, description: "Erro interno do servidor")
        ]
    )]
    private function get(?int $id = null): void {
        if ($id) {
            $animal = $this->animalModel->getById($id);
            if ($animal) {
                $this->response(true, "Animal encontrado.", $animal, 200);
            } else {
                $this->response(false, "Animal não encontrado.", null, 404);
            }
        } else {
            /** @var string|null $status */
            $status = $_GET['status'] ?? null;
            $animais = $this->animalModel->getAll($status);
            $this->response(true, "Lista de animais recuperada com sucesso.", $animais, 200);
        }
    }

    #[OA\Post(
        path: "/animais",
        summary: "Registro de novo animal para adoção",
        tags: ["Animais"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/AnimalInput")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Animal cadastrado com sucesso",
                content: new OA\JsonContent(ref: "#/components/schemas/Animais")
            ),
            new OA\Response(
                response: 400,
                description: "Campos obrigatórios ausentes",
                content: new OA\JsonContent(ref: "#/components/schemas/Animais")
            ),
            new OA\Response(response: 500, description: "Erro ao cadastrar animal")
        ]
    )]
    private function post(?int $id = null): void {
        /** @var array<string, mixed>|null $data */
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['nome']) || empty($data['especie']) || empty($data['raca']) || !isset($data['idade'])) {
            $this->response(false, "Campos obrigatórios ausentes: nome, especie, raca, idade.", null, 400);
            return;
        }

        $newId = $this->animalModel->create($data);
        if ($newId) {
            $this->response(true, "Animal cadastrado com sucesso!", ["id" => $newId], 201);
        } else {
            $this->response(false, "Erro ao cadastrar o animal.", null, 500);
        }
    }

    #[OA\Put(
        path: "/animais/{id}",
        summary: "Atualizar dados de um animal",
        tags: ["Animais"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/AnimalUpdateInput")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Dados do animal atualizados com sucesso",
                content: new OA\JsonContent(ref: "#/components/schemas/Animais")
            ),
            new OA\Response(
                response: 400,
                description: "Dados insuficientes para atualização",
                content: new OA\JsonContent(ref: "#/components/schemas/Animais")
            ),
            new OA\Response(
                response: 404,
                description: "Animal não encontrado",
                content: new OA\JsonContent(ref: "#/components/schemas/Animais")
            ),
            new OA\Response(response: 500, description: "Erro ao atualizar dados")
        ]
    )]
    private function put(?int $id = null): void {
        if (!$id) {
            $this->response(false, "ID do animal não fornecido.", null, 400);
            return;
        }

        if (!$this->animalModel->getById($id)) {
            $this->response(false, "Animal não encontrado.", null, 404);
            return;
        }

        /** @var array<string, mixed>|null $data */
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

    #[OA\Delete(
        path: "/animais/{id}",
        summary: "Exclusão de um animal",
        tags: ["Animais"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Animal removido do sistema com sucesso",
                content: new OA\JsonContent(ref: "#/components/schemas/Animais")
            ),
            new OA\Response(
                response: 404,
                description: "Animal não encontrado para remoção",
                content: new OA\JsonContent(ref: "#/components/schemas/Animais")
            ),
            new OA\Response(response: 500, description: "Erro interno do servidor")
        ]
    )]
    private function delete(?int $id = null): void {
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

    private function response(bool $status, string $message, mixed $data = null, int $code = 200): void {
        http_response_code($code);
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}