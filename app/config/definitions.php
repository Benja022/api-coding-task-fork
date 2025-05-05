<?php

namespace App\Config;

use App\Character\Application\CreateCharacterUseCase;
use App\Character\Application\DeleteCharacterUseCase;
use App\Character\Application\ReadAllCharactersUseCase;
use App\Character\Application\ReadCharacterUseCase;
use App\Character\Application\UpdateCharacterUseCase;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\Cache\CachedMySQLCharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterRepository;
use App\Equipment\Application\CreateEquipmentUseCase;
use App\Equipment\Application\DeleteEquipmentUseCase;
use App\Equipment\Application\ReadAllEquipmentUseCase;
use App\Equipment\Application\ReadEquipmentUseCase;
use App\Equipment\Application\UpdateEquipmentUseCase;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\MySQLEquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Cache\CachedMySQLEquipmentRepository;
use App\Faction\Application\CreateFactionUseCase;
use App\Faction\Application\DeleteFactionUseCase;
use App\Faction\Application\ReadAllFactionsUseCase;
use App\Faction\Application\ReadFactionUseCase;
use App\Faction\Application\UpdateFactionUseCase;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Persistence\Cache\CachedMySQLFactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\MySQLFactionRepository;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Redis;

/**
 * This file is used to define the dependencies of the application.
 * It is used by the ContainerBuilder to build the container.
 *
 * @param ContainerBuilder $containerBuilder
 * @return void
 */

return function (ContainerBuilder $containerBuilder) {


    $containerBuilder->addDefinitions([
        PDO::class => function () {
            $env = $_ENV['APP_ENV'] ?? 'dev';

            if ($env === 'test') {
                $host = $_ENV['DB_TEST_HOST'] ?? 'db';
                $db   = $_ENV['DB_TEST_NAME'] ?? 'test';
                $user = $_ENV['DB_TEST_USER'] ?? 'root';
                $pass = $_ENV['DB_TEST_PASSWORD'] ?? 'root';
            } else {
                $host = $_ENV['DB_HOST'] ?? 'db';
                $db   = $_ENV['DB_NAME'] ?? 'lotr';
                $user = $_ENV['DB_USER'] ?? 'root';
                $pass = $_ENV['DB_PASSWORD'] ?? 'root';
            }

            $conn = new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8mb4",
                $user,
                $pass
            );


            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $conn;
        },

        /**
         * Redis is used to cache the characters.
         *
         * @return Redis
         */
        Redis::class => function () {
            return new Redis([
                'host' => $_ENV['REDIS_HOST'],
                'port' => (int) $_ENV['REDIS_PORT'],
            ]);
        },

        /**
         * Logger is used to log the messages.
         *
         * @return LoggerInterface
         */
        LoggerInterface::class => function () {
            if ((bool) $_ENV['DEBUG_MODE']) {
                $logger = new Logger('app');
                $logger->pushHandler(new StreamHandler(('php://stdout'), Level::Debug));

                return $logger;
            }


            return new NullLogger();
        },

        /**
         * CharacterRepository is used to get the characters from the database.
         *
         * @param ContainerInterface $c
         * @return CharacterRepository
         */
        CharacterRepository::class => function (ContainerInterface $c) {
            if ((bool) ((int) $_ENV['CACHE_ENABLED'])) {
                return new CachedMySQLCharacterRepository(
                    // Inject the PDO connection from the container instead of creating a new one
                    new MySQLCharacterRepository($c->get(PDO::class)),
                    $c->get(Redis::class),
                    $c->get(LoggerInterface::class),

                );
            }

            return new MySQLCharacterRepository(
                $c->get(PDO::class)
            );
        },

        /**
         * CreateCharacterUseCase is used to create a new character.
         *
         * @param ContainerInterface $c
         * @return CreateCharacterUseCase
         */
        CreateCharacterUseCase::class => function (ContainerInterface $c) {
            return new CreateCharacterUseCase(
                $c->get(CharacterRepository::class)
            );
        },

        /**
         * ReadCharacterUseCase is used to read a character by id.
         *
         * @param ContainerInterface $c
         * @return ReadCharacterUseCase
         */
        ReadCharacterUseCase::class => function (ContainerInterface $c) {
            return new ReadCharacterUseCase(
                $c->get(CharacterRepository::class)
            );
        },
        // Read all characters
        ReadAllCharactersUseCase::class => function (ContainerInterface $c) {
            return new ReadAllCharactersUseCase(
                $c->get(CharacterRepository::class)
            );
        },
        // Delete a character by id
        DeleteCharacterUseCase::class => function (ContainerInterface $c) {
            return new DeleteCharacterUseCase(
                $c->get(CharacterRepository::class)
            );
        },
        // Update a character by id
        UpdateCharacterUseCase::class => function (ContainerInterface $c) {
            return new UpdateCharacterUseCase(
                $c->get(CharacterRepository::class)
            );
        },
        // Define the EquipmentRepository
        EquipmentRepository::class => function (ContainerInterface $c) {
            if ((bool) ((int) $_ENV['CACHE_ENABLED'])) {
                return new CachedMySQLEquipmentRepository(
                    new MySQLEquipmentRepository($c->get(PDO::class)),
                    $c->get(Redis::class),
                    $c->get(LoggerInterface::class)
                );
            }
            return new MySQLEquipmentRepository($c->get(PDO::class));
        },
        // Create an equipment
        CreateEquipmentUseCase::class => function (ContainerInterface $c) {
            return new CreateEquipmentUseCase(
                $c->get(EquipmentRepository::class)
            );
        },
        // Read an equipment by id
        ReadEquipmentUseCase::class => function (ContainerInterface $c) {
            return new ReadEquipmentUseCase(
                $c->get(EquipmentRepository::class)
            );
        },
        // Update an equipment by id
        UpdateEquipmentUseCase::class => function (ContainerInterface $c) {
            return new UpdateEquipmentUseCase(
                $c->get(EquipmentRepository::class)
            );
        },
        // Read all equipments
        ReadAllEquipmentUseCase::class => function (ContainerInterface $c) {
            return new ReadAllEquipmentUseCase(
                $c->get(EquipmentRepository::class)
            );
        },
        // Delete an equipment by id
        DeleteEquipmentUseCase::class => function (ContainerInterface $c) {
            return new DeleteEquipmentUseCase(
                $c->get(EquipmentRepository::class)
            );
        },


        // Define the FactionRepository
        FactionRepository::class => function (ContainerInterface $c) {
            if ((bool) ((int) $_ENV['CACHE_ENABLED'])) {
                return new CachedMySQLFactionRepository(
                    new MySQLFactionRepository($c->get(PDO::class)),
                    $c->get(Redis::class),
                    $c->get(LoggerInterface::class)
                );
            }
            return new MySQLFactionRepository($c->get(PDO::class));
        },
        // Create a faction
        CreateFactionUseCase::class => function (ContainerInterface $c) {
            return new CreateFactionUseCase(
                $c->get(FactionRepository::class)
            );
        },
        // Read a faction by id
        ReadFactionUseCase::class => function (ContainerInterface $c) {
            return new ReadFactionUseCase(
                $c->get(FactionRepository::class)
            );
        },
        // Read all factions
        ReadAllFactionsUseCase::class => function (ContainerInterface $c) {
            return new ReadAllFactionsUseCase(
                $c->get(FactionRepository::class)
            );
        },
        // Update a faction by id
        UpdateFactionUseCase::class => function (ContainerInterface $c) {
            return new UpdateFactionUseCase(
                $c->get(FactionRepository::class)
            );
        },
        // Delete a faction by id
        DeleteFactionUseCase::class => function (ContainerInterface $c) {
            return new DeleteFactionUseCase(
                $c->get(FactionRepository::class)
            );
        }
    ]);
};
