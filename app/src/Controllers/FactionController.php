<?php

namespace App\Controllers;

use App\Database\Database;

/**
 * Controlador para gestionar facciones de la saga El Señor de los Anillos
 *
 * Este controlador maneja las operaciones CRUD relacionadas con las diferentes
 * facciones que existen en la Tierra Media. Permite crear, consultar, actualizar
 * y eliminar facciones, asegurando la integridad de los datos y validando
 * las entradas según las reglas del negocio.
 *
 * @package App\Controllers
 * @version 1.0.0
 */
class FactionController {
    /** 
     * @var \PDO Instancia de la conexión a la base de datos 
     */
    private $db;

    /**
     * Constructor del controlador de facciones
     *
     * Inicializa la conexión a la base de datos utilizando el patrón Singleton
     * para asegurar una única instancia de la conexión.
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crea una nueva facción en la base de datos
     *
     * Este método valida y procesa los datos recibidos para crear una nueva facción.
     * Realiza las siguientes validaciones:
     * - Verifica que todos los campos requeridos estén presentes
     * - Comprueba que no exista otra facción con el mismo nombre
     * - Valida que la descripción tenga al menos 10 caracteres
     *
     * @throws \Exception Si faltan campos requeridos (código 400)
     * @throws \Exception Si ya existe una facción con el mismo nombre (código 409)
     * @throws \Exception Si la descripción es muy corta (código 400)
     * @throws \Exception Si hay un error en la base de datos (código 500)
     *
     * @return void
     *
     * @example
     * Ejemplo de JSON esperado en la petición:
     * {
     *     "faction_name": "Elfos de Rivendell",
     *     "description": "Una de las últimas casas acogedoras al este del mar. Hogar de la sabiduría élfica en la Tierra Media."
     * }
     *
     * Ejemplo de respuesta exitosa (código 201):
     * {
     *     "message": "Facción creada exitosamente",
     *     "id": 1
     * }
     */
    public function create() {
        // Obtener datos del cuerpo de la petición
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar datos requeridos
        $requiredFields = ['faction_name', 'description'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("El campo '$field' es requerido", 400);
            }
        }

        try {
            // Verificar si ya existe una facción con el mismo nombre
            $stmt = $this->db->prepare("SELECT id FROM factions WHERE faction_name = ?");
            $stmt->execute([$data['faction_name']]);
            if ($stmt->fetch()) {
                throw new \Exception("Ya existe una facción con el nombre '{$data['faction_name']}'", 409);
            }

            // Validar longitud de la descripción
            if (strlen($data['description']) < 10) {
                throw new \Exception("La descripción debe tener al menos 10 caracteres", 400);
            }

            // Insertar la nueva facción
            $stmt = $this->db->prepare("
                INSERT INTO factions (faction_name, description)
                VALUES (?, ?)
            ");

            $stmt->execute([
                $data['faction_name'],
                $data['description']
            ]);

            $factionId = $this->db->lastInsertId();

            // Devolver respuesta exitosa
            http_response_code(201);
            echo json_encode([
                'message' => 'Facción creada exitosamente',
                'id' => $factionId
            ]);

        } catch (\PDOException $e) {
            throw new \Exception("Error al crear la facción: " . $e->getMessage(), 500);
        }
    }
} 