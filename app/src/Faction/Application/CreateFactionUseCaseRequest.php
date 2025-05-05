<?php

namespace App\Faction\Application;

/**
 * This class generate a request with the faction data.
 *
 * @api
 * @package App\Faction\Application
 * @author Your Name <your.email@example.com>
 * @since 1.0.0
 */
class CreateFactionUseCaseRequest
{
    /**
     * @param string $factionName The name of the faction
     * @param string $description The description of the faction
     */
    public function __construct(
        private readonly string $faction_name,
        private readonly string $description
    ) {}

    /**
     * Gets the faction name
     *
     * @return string The faction name
     */
    public function getFactionName(): string
    {
        return $this->faction_name;
    }

    /**
     * Gets the faction description
     *
     * @return string The faction description
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
