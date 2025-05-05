# Lord of the Rings API

A RESTful API built with Slim Framework for managing characters and factions from the Lord of the Rings universe.

## Features

- Create and manage characters
- Create and manage factions
- Create and manage equipments
- JSON-based API
- Input validation
- Duplicate checking for factions

## Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Docker and Docker Compose
- Composer (PHP package manager)

## Installation

1. Clone the repository:

```bash
git clone <repository-url>
cd api-coding-task-fork
```

2. Build the project using the Makefile:

```bash
make build
```

3. Start the Docker containers:

```bash
docker-compose up -d
```

or

```bash
docker-compose up
```

4. Run the tests to verify everything is working:

```bash
make test
```

5. (Optional) Generate API documentation:

```bash
make docs
```

## Examples API Endpoints

This project include a postman file to try de endpoints generated, as JSON.

### Characters

#### Create a Character

- **URL**: `/characters`
- **Method**: `POST`
- **Content-Type**: `application/json`
- **Request Body**:

```json
{
  "name": "Aragorn",
  "birth_date": "2931-03-01",
  "kingdom": "Gondor",
  "equipment_id": 1,
  "faction_id": 1
}
```

- **Success Response**:
  - **Code**: 201
  - **Content**:

```json
{
  "id": 1,
  "message": "Character created successfully"
}
```

### Factions

#### Create a Faction

- **URL**: `/factions`
- **Method**: `POST`
- **Content-Type**: `application/json`
- **Request Body**:

```json
{
  "faction_name": "GONDOR",
  "description": "Gondor is the most powerful kingdom of men in Middle-earth"
}
```

- **Success Response**:
  - **Code**: 201
  - **Content**:

```json
{
  "id": 1,
  "message": "Faction created successfully"
}
```

- **Error Response** (if faction already exists):
  - **Code**: 409
  - **Content**:

```json
{
  "error": "A faction with this name already exists",
  "existing_faction": {
    "id": 1,
    "faction_name": "GONDOR",
    "description": "Gondor is the most powerful kingdom of men in Middle-earth"
  }
}
```

#### Get All Factions

- **URL**: `/factions`
- **Method**: `GET`
- **Success Response**:
  - **Code**: 200
  - **Content**:

```json
{
  "factions": [
    {
      "id": 1,
      "faction_name": "GONDOR",
      "description": "Gondor is the most powerful kingdom of men in Middle-earth"
    }
  ]
}
```

## Error Responses

### 400 Bad Request

```json
{
  "error": "Request body must be valid JSON"
}
```

or

```json
{
  "error": "Missing required field: field_name"
}
```

### 500 Internal Server Error

```json
{
  "error": "Failed to create faction",
  "message": "Error details"
}
```

## Database Schema

### Characters Table

- `id` (int, primary key, auto-increment)
- `name` (varchar(128))
- `birth_date` (date)
- `kingdom` (varchar(128))
- `equipment_id` (int, foreign key)
- `faction_id` (int, foreign key)

### Factions Table

- `id` (int, primary key, auto-increment)
- `faction_name` (varchar(128))
- `description` (text)

### Equipments Table

- `id` (int, primary key, auto-increment)
- `name` (varchar(128))
- `type` (varchar(128))
- `made_by` (varchar(128))
