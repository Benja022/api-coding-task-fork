<?php

namespace App\Faction\Domain;

use App\Faction\Domain\Exception\FactionValidationException;

/**
 * Faction is a class that represents a faction.
 * @api
 * @package App\Faction\Domain
 */
class Faction
{
    // Properties
    private string $faction_name;
    private string $description;
    private ?int $id = null;

    /**
     * @api
     * @param string $faction_name
     * @param string $description
     * @param int|null $id
     * @throws FactionValidationException
     */
    public function __construct(
        string $faction_name,
        string $description,
        ?int $id = null
    ) {
        if (empty($faction_name)) {
            throw FactionValidationException::factionNameRequired();
        }

        if (empty($description)) {
            throw FactionValidationException::factionDescriptionRequired();
        }

        if ($id !== null && $id <= 0) {
            throw FactionValidationException::idNonPositive();
        }

        $this->faction_name = $faction_name;
        $this->description = $description;
        $this->id = $id;
    }

    /**
     * @api
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @api
     * @return string
     */
    public function getFactionName(): string
    {
        return $this->faction_name;
    }

    /**
     * @api
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
