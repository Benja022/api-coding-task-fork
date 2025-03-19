Voy a revisar los archivos de inicialización de la base de datos que se encuentran en el directorio `./opt/db` para ver el esquema:

Ran tool
Encontré el archivo `init.sql` que contiene el esquema. Voy a revisarlo:

Read file: opt/db/init.sql
En DataGrip, puedes ver el esquema de varias formas:

1. **Vista automática del esquema**:
   - Una vez conectado, en el panel de la izquierda verás la base de datos `lotr`
   - Expande la base de datos y verás las tablas:
     - `characters`
     - `equipments`
     - `factions`

2. **Para ver el DDL (estructura) de las tablas**:
   - Click derecho en cualquier tabla
   - Selecciona "Show DDL" o "SQL Scripts" > "DDL Scripts"

El esquema consiste en 3 tablas principales:

1. **Tabla `factions`**:
   - `id` (int, auto-incrementable, llave primaria)
   - `faction_name` (varchar(128))
   - `description` (text)

2. **Tabla `equipments`**:
   - `id` (int, auto-incrementable, llave primaria)
   - `name` (varchar(128))
   - `type` (varchar(128))
   - `made_by` (varchar(128))

3. **Tabla `characters`**:
   - `id` (int, auto-incrementable, llave primaria)
   - `name` (varchar(128))
   - `birth_date` (date)
   - `kingdom` (varchar(128))
   - `equipment_id` (int, llave foránea a `equipments.id`)
   - `faction_id` (int, llave foránea a `factions.id`)

Las relaciones son:
- Un personaje pertenece a una facción (characters -> factions)
- Un personaje tiene un equipo (characters -> equipments)

También hay datos iniciales insertados para:
- Una facción (Mordor)
- Un equipo (Maza de Sauron)
- Un personaje (Sauron)
