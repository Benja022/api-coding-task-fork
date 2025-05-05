<?php

namespace App\Test\Character\Infrastructure\Persistence\Cache;

use App\Character\Domain\Character;
use App\Character\Infrastructure\Persistence\Cache\CachedMySQLCharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterRepository;
use PDO;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;
use Redis;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Before;


class CachedMySQLCharacterRepositoryTest extends TestCase
{

    private Redis $redis; // Mocked Redis instance
    private CachedMySQLCharacterRepository $repository; // The repository to test
    private int $equipmentId;
    private int $factionId;

    #[Before]
    public function init(): void // New Setup method
    {
        $this->redis = new Redis();
        $this->redis->connect("cache", 6379);
        $this->redis->flushAll(); // Clear the Redis cache before each test

        $pdo = new PDO("mysql:host=db;dbname=test", "root", "root");

        // Insert equipment and get id
        $pdo->exec("INSERT INTO equipments (name, type, made_by) VALUES ('Test Equipment', 'weapon', 'test')");
        $equipmentId = (int)($pdo->lastInsertId());

        // Insert faction and get id
        $pdo->exec("INSERT INTO factions (faction_name, description) VALUES ('Test Faction', 'Test Description')");
        $factionId = (int)($pdo->lastInsertId());


        // Save equipment and faction ids to use in tests
        $this->equipmentId = $equipmentId;
        $this->factionId = $factionId;

        $mySQLRepo = new MySQLCharacterRepository($pdo); // MySQL repository instance

        $this->repository = new CachedMySQLCharacterRepository($mySQLRepo, $this->redis, new NullLogger());

        error_reporting(E_ALL);
    }


    #[Test]
    #[Group('integration')]
    #[Group('cache')]
    public function testCharacterIsCachedAfterSave(): void
    {
        $character = new Character("John Doe", "1990-01-01", "Spain", $this->equipmentId, $this->factionId);
        $saved = $this->repository->save($character);

        $key = 'App\Character\Infrastructure\Persistence\Cache\CachedMySQLCharacterRepository:' . $saved->getId();
        $cached = $this->redis->get($key);

        $this->assertNotFalse($cached);
        $this->assertEquals($saved, unserialize($cached));
    }

    #[Test]
    #[Group('integration')]
    #[Group('cache')]
    public function testFindCachesResultWhenCacheMissOccurs(): void
    {
        $character = new Character("Cache Miss", "1991-02-02", "España", $this->equipmentId, $this->factionId);
        $saved = $this->repository->save($character);

        // Borramos el caché manualmente
        $key = 'App\Character\Infrastructure\Persistence\Cache\CachedMySQLCharacterRepository:' . $saved->getId();
        $this->redis->del($key);

        // Confirmamos que no está en Redis antes
        $this->assertFalse($this->redis->get($key));

        // Esto debería obtenerlo desde MySQL y cachearlo
        $fetched = $this->repository->find($saved->getId());

        $this->assertEquals($saved, $fetched);
        $this->assertNotFalse($this->redis->get($key)); // Confirmamos que se cacheó después
    }

    #[Test]
    #[Group('integration')]
    #[Group('cache')]
    public function testFindReturnsNullAndCachesItIfNotFound(): void
    {
        $nonExistentId = 99999;

        try {
            $result = $this->repository->find($nonExistentId);
        } catch (\App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException $e) {
            $this->assertSame('Character not found', $e->getMessage());

            $key = 'App\Character\Infrastructure\Persistence\Cache\CachedMySQLCharacterRepository:' . $nonExistentId;
            $this->assertFalse($this->redis->get($key)); // Asegura que no se cachea

            return; // Test pasó, salimos
        }
        $this->fail('Expected CharacterNotFoundException was not thrown');
    }

    #[Test]
    #[Group('integration')]
    #[Group('cache')]
    public function testSaveUpdateDeletesFindAllCache(): void
    {
        $character = new Character("John Doe", "1990-01-01", "Spain", $this->equipmentId, $this->factionId);
        $saved = $this->repository->save($character);

        $this->repository->findAll(); // Calienta cache
        $allKey = 'App\Character\Infrastructure\Persistence\Cache\CachedMySQLCharacterRepository:all';
        $this->assertNotFalse($this->redis->get($allKey));

        // Simula modificación y re-save (update)
        $refClass = new \ReflectionClass($saved);
        $nameProp = $refClass->getProperty('name');
        $nameProp->setAccessible(true);
        $nameProp->setValue($saved, "Updated Name");

        $this->repository->save($saved);

        $this->assertFalse($this->redis->get($allKey)); // cache invalidado
    }

    #[Test]
    #[Group('integration')]
    #[Group('cache')]
    public function testFindAllReturnsFromCacheAndLogsIt(): void
    {
        $loggedMessages = [];

        $mock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $mock->method('info')->willReturnCallback(function (string $message) use (&$loggedMessages) {
            $loggedMessages[] = $message;
        });

        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $mock;

        $repo = new CachedMySQLCharacterRepository(
            new \App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterRepository(
                new \PDO("mysql:host=db;dbname=test", "root", "root")
            ),
            $this->redis,
            $logger
        );

        $character = new Character("FindAll Cached", "2000-01-01", "España", $this->equipmentId, $this->factionId);
        $repo->save($character);

        $repo->findAll(); // cache miss → se guarda en Redis

        $repo->findAll(); // cache hit → debe loguear

        $this->assertContains('Getting all characters from cache', $loggedMessages);
    }


    #[Test]
    #[Group('integration')]
    #[Group('cache')]
    public function testDeleteRemovesFromCache(): void
    {
        $character = new Character("John Doe", "1990-01-01", "Spain", $this->equipmentId, $this->factionId);
        $saved = $this->repository->save($character);

        $key = 'App\Character\Infrastructure\Persistence\Cache\CachedMySQLCharacterRepository:' . $saved->getId();
        $this->assertNotFalse($this->redis->get($key));

        $this->repository->delete($saved);

        $this->assertFalse($this->redis->get($key));
    }

    #[Test]
    #[Group('integration')]
    #[Group('cache')]
    public function testLoggerIsCalledOnCacheHit(): void
    {
        $mensajesLog = [];

        $mock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $mock->method('info')->willReturnCallback(function (string $mensaje, array $contexto = []) use (&$mensajesLog) {
            $mensajesLog[] = $mensaje;
        });

        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $mock;

        $repo = new CachedMySQLCharacterRepository(
            new \App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterRepository(
                new \PDO("mysql:host=db;dbname=test", "root", "root")
            ),
            $this->redis,
            $logger
        );

        $character = new Character("John Doe", "1990-01-01", "Spain", $this->equipmentId, $this->factionId);
        $saved = $repo->save($character); // esto loguea "Character saved in cache"

        $repo->find($saved->getId()); // cache miss → no loguea "found"
        $repo->find($saved->getId()); // cache hit → debe loguear "Character found in cache"

        $this->assertContains('Character found in cache', $mensajesLog);
    }

    #[After]
    protected function cleanUp(): void // New TearDown method
    {
        $this->redis->flushAll(); // Clear the Redis cache after each test
    }
}
