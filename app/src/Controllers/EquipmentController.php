<?php

namespace App\Controllers;

use App\Database\Database;

class EquipmentController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create() {
        // Obtener datos del cuerpo de la petición
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar datos requeridos
        $requiredFields = ['name', 'type', 'made_by'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("El campo '$field' es requerido", 400);
            }
        }

        try {
            // Verificar si ya existe un equipo con el mismo nombre
            $stmt = $this->db->prepare("SELECT id FROM equipments WHERE name = ?");
            $stmt->execute([$data['name']]);
            if ($stmt->fetch()) {
                throw new \Exception("Ya existe un equipo con el nombre '{$data['name']}'", 409);
            }

            // Validar que el tipo sea válido (podemos añadir una lista de tipos permitidos)
            $validTypes = ['arma', 'armadura', 'objeto mágico', 'herramienta'];
            if (!in_array(strtolower($data['type']), $validTypes)) {
                throw new \Exception("Tipo de equipo no válido. Tipos permitidos: " . implode(', ', $validTypes), 400);
            }

            // Insertar el nuevo equipo
            $stmt = $this->db->prepare("
                INSERT INTO equipments (name, type, made_by)
                VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $data['name'],
                $data['type'],
                $data['made_by']
            ]);

            $equipmentId = $this->db->lastInsertId();

            // Devolver respuesta exitosa
            http_response_code(201);
            echo json_encode([
                'message' => 'Equipo creado exitosamente',
                'id' => $equipmentId
            ]);

        } catch (\PDOException $e) {
            throw new \Exception("Error al crear el equipo: " . $e->getMessage(), 500);
        }
    }
} 