<?php
namespace Model;

use PDO;
use PDOException;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class Connection {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8mb4";
                self::$instance = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["status" => false, "message" => "Erro de Conexão: " . $e->getMessage()]);
                exit;
            }
        }
        return self::$instance;
    }
}