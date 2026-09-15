<?php
session_start();
require_once "vendor/autoload.php";

use Model\AnimalModel;
use Controller\AnimalController;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


$file = __DIR__ . $path;
if (is_file($file) && file_exists($file)) {
    return false;
}

$parts = explode("/", trim($path, "/"));

$resource = !empty($parts[0]) ? $parts[0] : "animais";
$id = $parts[1] ?? null;

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
    http_response_code(500);
    echo json_encode(["error" => $error->getMessage()]);
}