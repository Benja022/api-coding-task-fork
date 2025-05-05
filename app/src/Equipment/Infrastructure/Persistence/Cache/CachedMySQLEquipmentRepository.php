<?php

namespace App\Equipment\Infrastructure\Persistence\Cache;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\MySQLEquipmentRepository;
use Psr\Log\LoggerInterface;
use Redis;

class CachedMySQLEquipmentRepository implements EquipmentRepository
{

    public function __construct(
        private MySQLEquipmentRepository $repository,
        private Redis $redis,
        private LoggerInterface $logger
    ) {}

    public function find(int $id): ?Equipment
    {
        $cacheKey = "equipment:{$id}";
        $cached = $this->redis->get($cacheKey);

        if ($cached) {
            $this->logger->debug("Cache hit for equipment {$id}");
            return unserialize($cached);
        }

        $this->logger->debug("Cache miss for equipment {$id}");
        $equipment = $this->repository->find($id);

        return $equipment;
    }

    public function findAll(): array
    {
        $cacheKey = "equipments:all";
        $cached = $this->redis->get($cacheKey);

        if ($cached) {
            $this->logger->debug("Cache hit for all equipments");
            return unserialize($cached);
        }

        $this->logger->debug("Cache miss for all equipments");
        return $this->repository->findAll();
    }

    public function save(Equipment $equipment): Equipment
    {
        $savedEquipment = $this->repository->save($equipment);

        // Invalidate cache
        $this->redis->del("equipment:{$savedEquipment->getId()}");
        $this->redis->del("equipments:all");

        return $savedEquipment;
    }

    public function delete(Equipment $equipment): bool
    {
        $result = $this->repository->delete($equipment);

        if ($result && $equipment->getId()) {
            // Invalidate cache
            $this->redis->del("equipment:{$equipment->getId()}");
            $this->redis->del("equipments:all");
        }

        return $result;
    }
}
