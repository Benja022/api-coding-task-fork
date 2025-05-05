<?php

namespace App\Faction\Domain;

/**
 * FactionRepository es una interfaz para el repositorio de Faction.
 * Se utiliza para definir los métodos que el repositorio debe implementar.
 *
 * @api
 * @package App\Faction\Domain
 */

interface FactionRepository
{
    /**
     * @api
     * @param Faction $faction
     * @return Faction
     */
    public function save(Faction $faction): Faction;

    /**
     * @api
     * @param int $id
     * @return Faction|null
     */
    public function find(int $id): ?Faction;

    /**
     * @api
     * @return array<Faction>
     */
    public function findAll(): array;

    /**
     * @api
     * @param Faction $faction
     * @return bool
     */
    public function delete(Faction $faction): bool;
}
