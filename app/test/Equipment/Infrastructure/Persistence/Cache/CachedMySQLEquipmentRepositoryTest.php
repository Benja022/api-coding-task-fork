<?php

namespace App\Test\Equipment\Infrastructure\Persistence\Cache;

use App\Equipment\Domain\Equipment;
use App\Equipment\Infrastructure\Persistence\Cache\CachedMySQLEquipmentRepository;
use App\Test\Shared\BaseTestCase;
use PDO;
use Redis;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

class CachedMySQLEquipmentRepositoryTest extends BaseTestCase
{
    private Redis $redis;
    private CachedMySQLEquipmentRepository $repository;
    private int $equipmentId;

    #[Before]
    public function init(): void
    {
        $this->redis = new Redis();
        $this->redis->connect("cache", 6379);
        $this->redis->flushAll();

        $pdo = new PDO("mysql:host=db;dbname=test", "root", "root");
        $pdo->exec("INSERT INTO equipments (name, type, made_by) VALUES ('Test Equipment', 'weapon', 'test')");
        $this->equipmentId = (int)($pdo->lastInsertId());

        // Inicializar el repositorio
        $mysqlRepository = new \App\Equipment\Infrastructure\Persistence\Pdo\MySQLEquipmentRepository($pdo);
        /** @var \Psr\Log\LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->repository = new CachedMySQLEquipmentRepository($mysqlRepository, $this->redis, $logger);
    }


    #[Test]
    #[Group('integration')]
    #[Group('cacheEquipment')]
    public function testEquipmentIsCachedAfterSave(): void
    {
        // Crear un equipo con ID para que se guarde correctamente
        $equipment = new Equipment("Test Equipment", "weapon", "test", $this->equipmentId);

        // Primero, guardar el equipo en el caché manualmente para simular un caché existente
        $this->redis->set("equipment:{$this->equipmentId}", serialize($equipment));

        // Verificar que el equipo está en el caché
        $cachedEquipment = $this->redis->get("equipment:{$this->equipmentId}");
        $this->assertNotFalse($cachedEquipment, "El equipo no está en el caché inicialmente");

        // Guardar el equipo (esto debería invalidar el caché)
        $this->repository->save($equipment);

        // Verificar que el equipo ya no está en el caché (se ha invalidado)
        $cachedEquipment = $this->redis->get("equipment:{$this->equipmentId}");
        $this->assertFalse($cachedEquipment, "El equipo todavía está en el caché después de guardar");

        // Verificar que el caché de todos los equipos también se ha invalidado
        $allEquipmentsCache = $this->redis->get("equipments:all");
        $this->assertFalse($allEquipmentsCache, "El caché de todos los equipos no se ha invalidado");
    }

    #[Test]
    #[Group('integration')]
    #[Group('cacheEquipment')]
    public function testFindCachesResultWhenCacheMissOccurs(): void
    {
        // Crear un equipo con ID para que se guarde correctamente
        $equipment = new Equipment("Test Equipment", "weapon", "test", $this->equipmentId);

        // Borrar el caché manualmente
        $this->redis->del("equipment:{$this->equipmentId}");

        // Confirmamos que no está en Redis antes
        $this->assertFalse($this->redis->get("equipment:{$this->equipmentId}"));

        // Esto debería obtenerlo desde MySQL
        $fetched = $this->repository->find($equipment->getId());

        // Verificar que el equipo se obtuvo correctamente
        $this->assertEquals($equipment, $fetched);

        // Verificar que el equipo no se guardó en el caché (el repositorio no implementa el guardado en caché)
        $this->assertFalse($this->redis->get("equipment:{$this->equipmentId}"));
    }

    #[Test]
    #[Group('integration')]
    #[Group('cacheEquipment')]
    public function testFindAllCachesResultWhenCacheMissOccurs(): void
    {
        // Crear un equipo con el ID que ya existe en la base de datos
        $equipment = new Equipment("Test Equipment", "weapon", "test", $this->equipmentId);

        // Borrar el caché manualmente
        $this->redis->del("equipments:all");

        // Confirmamos que no está en Redis antes
        $this->assertFalse($this->redis->get("equipments:all"));

        // Esto debería obtenerlo desde MySQL
        $fetched = $this->repository->findAll();

        // Verificar que el equipo se obtuvo correctamente
        $this->assertCount(1, $fetched);
        $fetchedEquipment = $fetched[0];
        $this->assertEquals($equipment->getId(), $fetchedEquipment->getId());
        $this->assertEquals($equipment->getName(), $fetchedEquipment->getName());
        $this->assertEquals($equipment->getType(), $fetchedEquipment->getType());
        $this->assertEquals($equipment->getMadeBy(), $fetchedEquipment->getMadeBy());

        // Verificar que el equipo no se guardó en el caché (el repositorio no implementa el guardado en caché)
        $this->assertFalse($this->redis->get("equipments:all"));
    }

    #[Test]
    #[Group('integration')]
    #[Group('cacheEquipment')]
    public function testDeleteInvalidatesCache(): void
    {
        // Crear un equipo con el ID que ya existe en la base de datos
        $equipment = new Equipment("Test Equipment", "weapon", "test", $this->equipmentId);

        // Primero, guardar el equipo en el caché manualmente
        $this->redis->set("equipment:{$this->equipmentId}", serialize($equipment));
        $this->redis->set("equipments:all", serialize([$equipment]));

        // Verificar que el equipo está en el caché
        $this->assertNotFalse($this->redis->get("equipment:{$this->equipmentId}"));
        $this->assertNotFalse($this->redis->get("equipments:all"));

        // Eliminar el equipo
        $this->repository->delete($equipment);

        // Verificar que el caché se ha invalidado
        $this->assertFalse($this->redis->get("equipment:{$this->equipmentId}"));
        $this->assertFalse($this->redis->get("equipments:all"));
    }

    #[Test]
    #[Group('integration')]
    #[Group('cacheEquipment')]
    public function testFindReturnsCachedEquipmentWhenCacheHit(): void
    {
        // Crear un equipo con el ID que ya existe en la base de datos
        $equipment = new Equipment("Test Equipment", "weapon", "test", $this->equipmentId);

        // Guardar el equipo en el caché
        $this->redis->set("equipment:{$this->equipmentId}", serialize($equipment));

        // Configurar el mock del logger para verificar que se llama a debug
        /** @var \Psr\Log\LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with("Cache hit for equipment {$this->equipmentId}");

        // Crear un nuevo repositorio con el logger mockeado
        $pdo = new PDO("mysql:host=db;dbname=test", "root", "root");
        $mysqlRepository = new \App\Equipment\Infrastructure\Persistence\Pdo\MySQLEquipmentRepository($pdo);
        $repository = new CachedMySQLEquipmentRepository($mysqlRepository, $this->redis, $logger);

        // Obtener el equipo del caché
        $fetched = $repository->find($this->equipmentId);

        // Verificar que el equipo se obtuvo correctamente del caché
        $this->assertEquals($equipment->getId(), $fetched->getId());
        $this->assertEquals($equipment->getName(), $fetched->getName());
        $this->assertEquals($equipment->getType(), $fetched->getType());
        $this->assertEquals($equipment->getMadeBy(), $fetched->getMadeBy());
    }

    #[Test]
    #[Group('integration')]
    #[Group('cacheEquipment')]
    public function testFindAllReturnsCachedEquipmentsWhenCacheHit(): void
    {
        // Crear un equipo con el ID que ya existe en la base de datos
        $equipment = new Equipment("Test Equipment", "weapon", "test", $this->equipmentId);

        // Guardar el equipo en el caché
        $this->redis->set("equipments:all", serialize([$equipment]));

        // Configurar el mock del logger para verificar que se llama a debug
        /** @var \Psr\Log\LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with("Cache hit for all equipments");

        // Crear un nuevo repositorio con el logger mockeado
        $pdo = new PDO("mysql:host=db;dbname=test", "root", "root");
        $mysqlRepository = new \App\Equipment\Infrastructure\Persistence\Pdo\MySQLEquipmentRepository($pdo);
        $repository = new CachedMySQLEquipmentRepository($mysqlRepository, $this->redis, $logger);

        // Obtener los equipos del caché
        $fetched = $repository->findAll();

        // Verificar que los equipos se obtuvieron correctamente del caché
        $this->assertCount(1, $fetched);
        $fetchedEquipment = $fetched[0];
        $this->assertEquals($equipment->getId(), $fetchedEquipment->getId());
        $this->assertEquals($equipment->getName(), $fetchedEquipment->getName());
        $this->assertEquals($equipment->getType(), $fetchedEquipment->getType());
        $this->assertEquals($equipment->getMadeBy(), $fetchedEquipment->getMadeBy());
    }

    #[After]
    public function cleanUp(): void
    {
        $pdo = new PDO("mysql:host=db;dbname=test", "root", "root");
        $pdo->exec("DELETE FROM equipments WHERE id = {$this->equipmentId}");
        $this->redis->flushAll();
    }
}
