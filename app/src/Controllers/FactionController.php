<?php

namespace App\Controllers;

use App\Database\Database;

class FactionController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

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