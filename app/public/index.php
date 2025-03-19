<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../src/bootstrap.php';

// Parse URL and method
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

// Basic routing
$resource = $uri[0] ?? '';
$id = $uri[1] ?? null;

// Route handling
try {
    switch ($resource) {
        case 'characters':
            $controller = new \App\Controllers\CharacterController();
            
            switch ($requestMethod) {
                case 'POST':
                    $controller->create();
                    break;
                default:
                    throw new \Exception('Método no permitido', 405);
            }
            break;
            
        case 'equipments':
            $controller = new \App\Controllers\EquipmentController();
            
            switch ($requestMethod) {
                case 'POST':
                    $controller->create();
                    break;
                default:
                    throw new \Exception('Método no permitido', 405);
            }
            break;

        case 'factions':
            $controller = new \App\Controllers\FactionController();
            
            switch ($requestMethod) {
                case 'POST':
                    $controller->create();
                    break;
                default:
                    throw new \Exception('Método no permitido', 405);
            }
            break;
            
        default:
            throw new \Exception('Recurso no encontrado', 404);
    }
} catch (\Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
