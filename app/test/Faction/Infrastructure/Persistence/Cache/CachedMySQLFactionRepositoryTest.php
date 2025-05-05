<?php

namespace App\Test\Faction\Infrastructure\Persistence\Cache;

use App\Faction\Domain\Faction;
use App\Test\Shared\BaseTestCase;
use App\Faction\Infrastructure\Persistence\Cache\CachedMySQLFactionRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Before;
use Redis;
use PDO;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class CachedMySQLFactionRepositoryTest extends BaseTestCase
{
    private Redis $redis;
    private CachedMySQLFactionRepository $repository;
    private int $factionId;
    private PDO&MockObject $pdo;
    private \PDOStatement&MockObject $statement;

    #[Before]
    public function init(): void
    {
        $this->redis = new Redis();
        $this->redis->connect("cache", 6379);
        $this->redis->flushAll();

        $this->pdo = $this->createMock(PDO::class);
        $this->pdo->method('lastInsertId')->willReturn('1');

        $this->statement = $this->createMock(\PDOStatement::class);
        $this->pdo->method('prepare')->willReturn($this->statement);

        $this->factionId = 1;  // Valor fijo para los tests
    }

    #[Test]
    #[Group('integration')]
    #[Group('cacheFaction')]
    public function testFactionIsCachedAfterSave(): void
    {
        /** @var \PDO&\PHPUnit\Framework\MockObject\MockObject $pdo */
        $mysqlRepository = new \App\Faction\Infrastructure\Persistence\Pdo\MySQLFactionRepository($this->pdo);
        /** @var LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $cachedRepository = new CachedMySQLFactionRepository($mysqlRepository, $this->redis, $logger);

        $faction = new Faction("Test Faction", "Test Description", $this->factionId);
        $cachedRepository->save($faction);

        $cachedFaction = $this->redis->get("faction:{$this->factionId}");
        $this->assertNotFalse($cachedFaction, "La facción no está en el caché inicialmente");
        $cachedFaction = unserialize($cachedFaction);
        $this->assertEquals($faction->getFactionName(), $cachedFaction->getFactionName());
        $this->assertEquals($faction->getDescription(), $cachedFaction->getDescription());
    }

    #[Test]
    #[Group('integration')]
    #[Group('cacheFaction')]
    public function testFindCachesResultWhenCacheMissOccurs(): void
    {
        /** @var \PDO&\PHPUnit\Framework\MockObject\MockObject $pdo */
        $mysqlRepository = new \App\Faction\Infrastructure\Persistence\Pdo\MySQLFactionRepository($this->pdo);
        /** @var LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $cachedRepository = new CachedMySQLFactionRepository($mysqlRepository, $this->redis, $logger);

        $faction = new Faction("Test Faction", "Test Description", $this->factionId);
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);
        $this->statement->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->statement->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'id' => $this->factionId,
                'faction_name' => "Test Faction",
                'description' => "Test Description"
            ]);

        $cachedRepository->find($this->factionId);

        $cachedFaction = $this->redis->get("faction:{$this->factionId}");
        $this->assertNotFalse($cachedFaction, "La facción no está en el caché después de encontrarla");
    }

    #[Test]
    #[Group('integration')]
    #[Group('cacheFaction')]
    public function testFindAllCachesResultWhenCacheMissOccurs(): void
    {
        /** @var \PDO&\PHPUnit\Framework\MockObject\MockObject $pdo */
        $mysqlRepository = new \App\Faction\Infrastructure\Persistence\Pdo\MySQLFactionRepository($this->pdo);
        /** @var LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $cachedRepository = new CachedMySQLFactionRepository($mysqlRepository, $this->redis, $logger);

        $faction = new Faction("Test Faction", "Test Description", $this->factionId);
        $this->pdo->expects($this->once())
            ->method('query')
            ->willReturn($this->statement);
        $this->statement->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => $this->factionId,
                    'faction_name' => "Test Faction",
                    'description' => "Test Description"
                ],
                false
            );

        $factions = $cachedRepository->findAll();
        $this->assertNotEmpty($factions, "No se encontraron facciones");

        $cachedFactions = $this->redis->get("factions:all");
        $this->assertNotFalse($cachedFactions, "Las facciones no están en el caché");
    }

    #[Test]
    #[Group('integration')]
    #[Group('cacheFaction')]
    public function testDeleteInvalidatesCache(): void
    {
        /** @var \PDO&\PHPUnit\Framework\MockObject\MockObject $pdo */
        $mysqlRepository = new \App\Faction\Infrastructure\Persistence\Pdo\MySQLFactionRepository($this->pdo);
        /** @var LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $cachedRepository = new CachedMySQLFactionRepository($mysqlRepository, $this->redis, $logger);

        $faction = new Faction("Test Faction", "Test Description", $this->factionId);
        $cachedRepository->save($faction);

        $cachedFaction = $this->redis->get("faction:{$this->factionId}");
        $this->assertNotFalse($cachedFaction, "La facción no está en el caché inicialmente");

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);
        $this->statement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $cachedRepository->delete($faction);

        $cachedFaction = $this->redis->get("faction:{$this->factionId}");
        $this->assertFalse($cachedFaction, "La facción sigue en el caché después de eliminarla");
    }

    #[Test]
    #[Group('integration')]
    #[Group('cacheFaction')]
    public function testFindReturnsCachedFactionWhenCacheHit(): void
    {
        /** @var \PDO&\PHPUnit\Framework\MockObject\MockObject $pdo */
        $mysqlRepository = new \App\Faction\Infrastructure\Persistence\Pdo\MySQLFactionRepository($this->pdo);
        /** @var LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $cachedRepository = new CachedMySQLFactionRepository($mysqlRepository, $this->redis, $logger);

        $faction = new Faction("Test Faction", "Test Description", $this->factionId);
        $this->redis->set("faction:{$this->factionId}", serialize($faction));

        $this->pdo->expects($this->never())
            ->method('prepare');

        $foundFaction = $cachedRepository->find($this->factionId);
        $this->assertNotNull($foundFaction, "La facción no se encontró en el caché");
        $this->assertEquals($this->factionId, $foundFaction->getId(), "El ID de la facción no coincide");
        $this->assertEquals("Test Faction", $foundFaction->getFactionName(), "El nombre de la facción no coincide");
        $this->assertEquals("Test Description", $foundFaction->getDescription(), "La descripción de la facción no coincide");
    }
}
