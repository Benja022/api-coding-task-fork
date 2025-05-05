<?php

namespace App\Character\Domain;

use App\Character\Domain\Exception\CharacterValidationException;

/**
 * Character entity representing a character in the game
 *
 * @api
 * @package App\Character\Domain
 */
class Character
{

    public function __construct(
        private string $name,
        private string $birth_date,
        private string $kingdom,
        private int $equipment_id,
        private int $faction_id,
        private ?int $id = null
    ) {
        //Validar los datos
        $this->validateName();
        $this->validateBirthDate();
        $this->validateKingdom();
        $this->validateEquipmentId();
        $this->validateFactionId();
    }

    private function validateName(): void
    {
        if (empty($this->name)) {
            throw CharacterValidationException::nameRequired();
        }
    }

    private function validateBirthDate(): void
    {
        if (empty($this->birth_date)) {
            throw CharacterValidationException::birthDateRequired();
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->birth_date)) {
            throw CharacterValidationException::birthDateInvalidFormat();
        }
    }

    private function validateKingdom(): void
    {
        if (empty($this->kingdom)) {
            throw CharacterValidationException::kingdomRequired();
        }
    }

    private function validateEquipmentId(): void
    {
        if (empty($this->equipment_id)) {
            throw CharacterValidationException::equipmentIdRequired();
        }
    }

    private function validateFactionId(): void
    {
        if (empty($this->faction_id)) {
            throw CharacterValidationException::factionIdRequired();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBirthDate(): string
    {
        return $this->birth_date;
    }

    public function getKingdom(): string
    {
        return $this->kingdom;
    }

    public function getEquipmentId(): int
    {
        return $this->equipment_id;
    }

    public function getFactionId(): int
    {
        return $this->faction_id;
    }
}
