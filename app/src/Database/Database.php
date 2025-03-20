<?php

namespace App\Database;

/**
 * Clase para gestionar la conexión a la base de datos usando PDO
 * 
 * Esta clase implementa el patrón Singleton para asegurar una única conexión
 * a la base de datos durante toda la ejecución de la aplicación. Utiliza PDO
 * (PHP Data Objects) para proporcionar una capa de abstracción de acceso a datos
 * segura y consistente.
 * 
 * Características de la conexión PDO:
 * - Manejo de errores mediante excepciones (PDO::ERRMODE_EXCEPTION)
 * - Resultados como arrays asociativos por defecto (PDO::FETCH_ASSOC)
 * - Soporte para caracteres UTF-8
 * - Prevención de inyección SQL mediante prepared statements
 * 
 * @package App\Database
 * @version 1.0.0
 * @link https://www.php.net/manual/es/book.pdo.php Documentación oficial de PDO
 */
class Database {
    /** 
     * @var Database|null Instancia única de la clase Database
     * @static 
     */
    private static $instance = null;

    /** 
     * @var \PDO Conexión a la base de datos
     * 
     * La conexión PDO proporciona:
     * - Prepared statements para prevenir inyección SQL
     * - Transacciones para operaciones atómicas
     * - Manejo de errores mediante excepciones
     */
    private $connection;

    /**
     * Constructor privado para prevenir la creación directa de objetos
     * 
     * Establece una conexión PDO con la base de datos MySQL usando las constantes:
     * - DB_HOST: Hostname del servidor de base de datos
     * - DB_NAME: Nombre de la base de datos
     * - DB_USER: Usuario de la base de datos
     * - DB_PASS: Contraseña del usuario
     * 
     * Configuración de PDO:
     * - PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION: Lanza excepciones en caso de error
     * - PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC: Retorna arrays asociativos
     * - PDO::MYSQL_ATTR_INIT_COMMAND: Configura la conexión en UTF-8
     * 
     * @throws \Exception Si hay un error en la conexión a la base de datos
     */
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

    /**
     * Obtiene la instancia única de la clase Database
     * 
     * Este método implementa el patrón Singleton, asegurando que solo exista
     * una instancia de la conexión a la base de datos durante toda la ejecución.
     * 
     * @return Database Instancia única de Database
     * @static
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtiene la conexión PDO a la base de datos
     * 
     * Retorna el objeto PDO que permite:
     * - Ejecutar consultas SQL
     * - Preparar statements
     * - Manejar transacciones
     * - Obtener información del servidor
     * 
     * @return \PDO Conexión activa a la base de datos
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Previene la clonación del objeto
     * 
     * Este método es privado para mantener el patrón Singleton y evitar
     * la duplicación de conexiones a la base de datos.
     * 
     * @return void
     * @throws \Exception Siempre lanza una excepción
     */
    private function __clone() {
        throw new \Exception("No se permite clonar esta instancia");
    }

    /**
     * Previene la deserialización del objeto
     * 
     * Este método es público (requerido por PHP 8.0+) para mantener el patrón
     * Singleton y evitar la recreación de instancias mediante deserialización.
     * 
     * @return void
     * @throws \Exception Siempre lanza una excepción
     */
    public function __wakeup() {
        throw new \Exception("No se permite deserializar esta instancia");
    }
} 