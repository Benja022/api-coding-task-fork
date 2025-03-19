<?php

namespace App\Controllers;

use App\Database\Database;

class CharacterController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create() {
        // Obtener datos del cuerpo de la petición
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar datos requeridos
        $requiredFields = ['name', 'birth_date', 'kingdom', 'equipment_id', 'faction_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("El campo '$field' es requerido", 400);
            }
        }

        try {
            // Verificar si ya existe un personaje con el mismo nombre
            $stmt = $this->db->prepare("SELECT id FROM characters WHERE name = ?");
            $stmt->execute([$data['name']]);
            if ($stmt->fetch()) {
                throw new \Exception("Ya existe un personaje con el nombre '{$data['name']}'", 409);
            }

            // Validar que el equipment_id existe
            $stmt = $this->db->prepare("SELECT id FROM equipments WHERE id = ?");
            $stmt->execute([$data['equipment_id']]);
            if (!$stmt->fetch()) {
                throw new \Exception("El equipment_id no existe", 400);
            }

            // Validar que el faction_id existe
            $stmt = $this->db->prepare("SELECT id FROM factions WHERE id = ?");
            $stmt->execute([$data['faction_id']]);
            if (!$stmt->fetch()) {
                throw new \Exception("El faction_id no existe", 400);
            }

            // Validar formato de fecha
            if (!strtotime($data['birth_date'])) {
                throw new \Exception("El formato de birth_date es inválido. Use YYYY-MM-DD", 400);
            }

            // Insertar el nuevo personaje
            $stmt = $this->db->prepare("
                INSERT INTO characters (name, birth_date, kingdom, equipment_id, faction_id)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['name'],
                $data['birth_date'],
                $data['kingdom'],
                $data['equipment_id'],
                $data['faction_id']
            ]);

            $characterId = $this->db->lastInsertId();

            // Devolver respuesta exitosa
            http_response_code(201);
            echo json_encode([
                'message' => 'Personaje creado exitosamente',
                'id' => $characterId
            ]);

        } catch (\PDOException $e) {
            throw new \Exception("Error al crear el personaje: " . $e->getMessage(), 500);
        }
    }
} 