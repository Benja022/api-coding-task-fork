<?php

namespace App\Faction\Infrastructure\Persistence\Cache;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\MySQLFactionRepository;
use Psr\Log\LoggerInterface;
use Redis;

/**
 * CachedMySQLFactionRepository es una clase que se utiliza para cachear el repositorio de facciones.
 *
 * @api
 * @package App\Faction\Infrastructure\Persistence\Cache
 */
class CachedMySQLFactionRepository implements FactionRepository
{
    /**
     * @api
     * @param MySQLFactionRepository $repository
     * @param Redis $redis
     * @param LoggerInterface $logger
     */
    public function __construct(
        private MySQLFactionRepository $repository,
        private Redis $redis,
        private LoggerInterface $logger
    ) {}

    /**
     * @api
     * @param int $id
     * @return Faction|null
     */
    public function find(int $id): ?Faction
    {
        $cacheKey = "faction:{$id}";
        $cached = $this->redis->get($cacheKey);

        if ($cached) {
            $this->logger->debug("Cache hit for faction {$id}");
            return unserialize($cached);
        }

        $this->logger->debug("Cache miss for faction {$id}");
        $faction = $this->repository->find($id);

        if ($faction) {
            $this->redis->set($cacheKey, serialize($faction));
        }

        return $faction;
    }

    /**
     * @api
     * @return array<Faction>
     */
    public function findAll(): array
    {
        $cacheKey = "factions:all";
        $cached = $this->redis->get($cacheKey);

        if ($cached) {
            $this->logger->debug("Cache hit for all factions");
            return unserialize($cached);
        }

        $this->logger->debug("Cache miss for all factions");
        $factions = $this->repository->findAll();

        if (!empty($factions)) {
            $this->redis->set($cacheKey, serialize($factions));
        }

        return $factions;
    }

    /**
     * @api
     * @param Faction $faction
     * @return Faction
     */
    public function save(Faction $faction): Faction
    {
        $savedFaction = $this->repository->save($faction);

        // Guardar en caché
        $this->redis->set("faction:{$savedFaction->getId()}", serialize($savedFaction));
        // Invalidar el caché de todas las facciones
        $this->redis->del("factions:all");

        return $savedFaction;
    }

    /**
     * @api
     * @param Faction $faction
     * @return bool
     */
    public function delete(Faction $faction): bool
    {
        $result = $this->repository->delete($faction);

        if ($result && $faction->getId()) {
            // Invalidate cache
            $this->redis->del("faction:{$faction->getId()}");
            $this->redis->del("factions:all");
        }

        return $result;
    }
}
