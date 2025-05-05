#!/bin/bash

# Colores para la salida
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

# URL base
BASE_URL="http://localhost:8080"

# Función para probar endpoints GET
test_get_endpoint() {
    local endpoint=$1
    local description=$2
    echo "Probando GET $endpoint - $description"
    
    response=$(curl -s -w "\n%{http_code}" "$BASE_URL$endpoint")
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    if [[ $http_code =~ ^2 ]]; then
        echo -e "${GREEN}✓${NC} Status: $http_code"
        echo "Respuesta:"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    else
        echo -e "${RED}✗${NC} Status: $http_code"
        echo "Respuesta:"
        echo "$body"
    fi
    echo "----------------------------------------"
}

# Función para probar endpoints POST
test_post_endpoint() {
    local endpoint=$1
    local data=$2
    local description=$3
    echo "Probando POST $endpoint - $description"
    
    response=$(curl -s -w "\n%{http_code}" -X POST \
        -H "Content-Type: application/json" \
        -d "$data" \
        "$BASE_URL$endpoint")
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    if [[ $http_code =~ ^2 ]]; then
        echo -e "${GREEN}✓${NC} Status: $http_code"
        echo "Respuesta:"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    else
        echo -e "${RED}✗${NC} Status: $http_code"
        echo "Respuesta:"
        echo "$body"
    fi
    echo "----------------------------------------"
}

echo "Iniciando pruebas de API..."
echo "----------------------------------------"

# Probar GET /
test_get_endpoint "/" "Greeting"

# Probar endpoints de factions
test_get_endpoint "/factions" "Obtener todas las facciones"
test_post_endpoint "/factions" '{"name":"Riders of Rohan","description":"Skilled horsemen of Rohan"}' "Crear nueva facción"

# Probar endpoints de equipments
test_get_endpoint "/equipments" "Obtener todos los equipamientos"
test_post_endpoint "/equipments" '{"name":"Glamdring","type":"sword","made_by":"Elves"}' "Crear nuevo equipamiento"

# Probar endpoints de characters
test_get_endpoint "/characters" "Obtener todos los personajes"
test_get_endpoint "/characters/1" "Obtener personaje por ID"
test_post_endpoint "/characters" '{"name":"Aragorn","birth_date":"TA 2931","kingdom":"Gondor","faction_id":1,"equipment_ids":[1]}' "Crear nuevo personaje"

echo "Pruebas completadas." 