<?php

namespace App\Character\Infrastructure\Persistence\Cache;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterRepository;
use Psr\Log\LoggerInterface;
use Redis;


/**
 * This class is a wrapper around the MySQLCharacterRepository.
 * It caches the results of the findAll, find save and methods.
 * It uses a Redis instance to cache the results.
 * It also uses a LoggerInterface to log the cache hits and misses.
 */
class CachedMySQLCharacterRepository implements CharacterRepository
{



    public function __construct(
        private MySQLCharacterRepository $mySQLCharacterRepository,
        private Redis $redis,
        private ?LoggerInterface $logger
    ) {}

    /**
     * This method returns the key for the cache.
     * It is used to store the character in Redis.
     */
    private function getKey(string $key): string
    {
        return __CLASS__ . ':' . $key;
    }

    /**
     * This method finds a character by its id.
     * It first checks if the character is cached in Redis.
     * If it is, it returns the cached character.
     * If it is not, it fetches the character from the MySQL database, caches it in Redis and returns it.
     */
    public function find(int $id): ?Character
    {
        $cachedCharacter = $this->redis->get($this->getKey($id));
        if ($cachedCharacter) {
            $this->logger->info('Character found in cache', ['id' => $id]);

            return unserialize($cachedCharacter);
        }

        $character = $this->mySQLCharacterRepository->find($id);
        $this->redis->set($this->getKey($id), serialize($character));

        return $character;
    }

    /**
     * This method finds all characters.
     * It first checks if the characters are cached in Redis.
     * If they are, it returns the cached characters.
     * If they are not, it fetches the characters from the MySQL database, caches them in Redis and returns them.
     */
    public function findAll(): array
    {
        $cachedCharacters = $this->redis->get($this->getKey('all'));
        if ($cachedCharacters) {
            $this->logger->info('Getting all characters from cache');

            return unserialize($cachedCharacters);
        }

        $characters = $this->mySQLCharacterRepository->findAll();
        $this->redis->set($this->getKey('all'), serialize($characters));

        return $characters;
    }

    /**
     * This method saves and updates a character.
     * It first checks if the character is already in the MySQL database.
     * If it is, it updates the character.
     * If it is not, it saves the character.
     * Then it caches the character in Redis.
     */
    public function save(Character $character): Character
    {
        if ($character->getId() !== null) {
            //Update
            $updatedCharacter = $this->mySQLCharacterRepository->update($character);
            $this->redis->set($this->getKey($updatedCharacter->getId()), serialize($updatedCharacter));
            $this->redis->del($this->getKey('all'));
            if ($updatedCharacter) {
                $this->logger->info('Character updated in cache');
            }
            return $updatedCharacter;
        }
        // Insert ¿CALENTAR CACHE?
        $newCharacter = $this->mySQLCharacterRepository->save($character);
        $this->redis->set($this->getKey($newCharacter->getId()), serialize($newCharacter));
        $this->redis->del($this->getKey('all'));
        if ($newCharacter) {
            $this->logger->info('Character saved in cache');
        }
        return $newCharacter;
    }


    /**
     * This method deletes a character.
     * It first deletes the character from the MySQL database.
     * Then it deletes the character from the Redis cache.
     */
    public function delete(Character $character): bool
    {
        $this->mySQLCharacterRepository->delete($character);
        $this->redis->del($this->getKey($character->getId()));
        $this->redis->del($this->getKey('all'));

        return true;
    }
}

/* TODO: add a method to invalidate the cache when a character is updated
because the character is not updated in the cache,could be a problem by
 serializing and unserializing the character.
*/

/*
TODO: ¿Implement a TTL for the cache?
*/
