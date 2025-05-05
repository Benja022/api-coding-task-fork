<?php

namespace App\Equipment\Domain;

use App\Equipment\Domain\Exception\EquipmentValidationException;

/**
 * Equipment is a class that represents an equipment.
 *
 * @package App\Equipment\Domain
 */
class Equipment
{
    // Properties
    private string $name;
    private string $type;
    private string $made_by;
    private ?int $id = null;

    // Constructor
    public function __construct(
        string $name,
        string $type,
        string $made_by,
        ?int $id = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->made_by = $made_by;
        $this->id = $id;

        // Validar los datos
        $this->validateName();
        $this->validateType();
        $this->validateMadeBy();

        // Solo validar el ID si no es null
        if ($this->id !== null) {
            $this->validateId();
        }
    }

    private function validateName(): void
    {
        if (empty($this->name)) {
            throw EquipmentValidationException::nameRequired();
        }
    }

    private function validateType(): void
    {
        if (empty($this->type)) {
            throw EquipmentValidationException::typeRequired();
        }
    }

    private function validateMadeBy(): void
    {
        if (empty($this->made_by)) {
            throw EquipmentValidationException::madeByRequired();
        }
    }

    private function validateId(): void
    {
        if ($this->id <= 0) {
            throw EquipmentValidationException::idNonPositive();
        }
    }

    // Getters (seters are not needed because we are using semantic setters)
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMadeBy(): string
    {
        return $this->made_by;
    }
}
