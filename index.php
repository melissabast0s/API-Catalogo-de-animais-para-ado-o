<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Controller/AnimalController.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$uriSegments = explode('/', trim($uri, '/'));

if (isset($uriSegments[0]) && $uriSegments[0] === 'animais') {
    $controller = new AnimalController();
    $id = isset($uriSegments[1]) ? (int)$uriSegments[1] : null;

    switch ($method) {
        case 'GET':
            $controller->get($id);
            break;
        case 'POST':
            $controller->post();
            break;
        case 'PUT':
            $controller->put($id);
            break;
        case 'DELETE':
            $controller->delete($id);
            break;
        default:
            http_response_code(405);
            echo json_encode(["status" => false, "message" => "Método não permitido."]);
            break;
    }
} else {
    http_response_code(404);
    echo json_encode(["status" => false, "message" => "Rota não encontrada."]);
}