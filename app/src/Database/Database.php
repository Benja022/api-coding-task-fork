<?php

namespace App\Database;

class Database {
    // Propiedad estática para almacenar la instancia de la clase
    private static $instance = null;
    private $connection;

    // Constructor privado para evitar la creación directa de objetos
    private function __construct() {
        try {
            $this->connection = new \PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ]
            );
        } catch (\PDOException $e) {
            throw new \Exception("Error de conexión: " . $e->getMessage(), 500);
        }
    }

    // Método estático para obtener la instancia de la clase
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Método para obtener la conexión a la base de datos
    public function getConnection() {
        return $this->connection;
    }
} 