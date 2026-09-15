<?php
session_start();
require_once "vendor/autoload.php";

use Model\AnimalModel;
use Controller\AnimalController;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$parts = explode("/", $path);

$resource = $parts[1] ?? null; 
$id = $parts[2] ?? null;

header("Content-Type: application/json; charset=UTF-8");

if ($resource !== "animais") {
    http_response_code(404);
    echo json_encode(["error" => "Rota desconhecida!"]);
    exit;
}

try {
    $animalModel = new AnimalModel();
    $animalController = new AnimalController($animalModel);

    $animalController->ProcessRequest($_SERVER['REQUEST_METHOD'], $id);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Erro interno."]);
}