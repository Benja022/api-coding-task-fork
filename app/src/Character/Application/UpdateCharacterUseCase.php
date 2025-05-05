<?php

namespace App\Character\Application;

/**
 * This use case is used to update a character
 *
 * @api
 * @package App\Character\Application
 */

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;

class UpdateCharacterUseCase
{
    public function __construct(
        private CharacterRepository $repository
    ) {}

    public function execute(
        UpdateCharacterUseCaseRequest $request
    ): Character {
        $oldcharacter = $this->repository->find($request->getId());


        // This rule could be removed if we use a DTO to validate the request,
        // in this case, the request is validated by ArrayCharacterRepositoryTest
        // and MySQLCharacterRepository, this is redundant but we keep it for now.s
        if (!$oldcharacter) {
            throw new CharacterNotFoundException('Character not found');
        }

        $updatedcharacter = new Character(
            name: $request->getName(),
            birth_date: $request->getBirthDate(),
            kingdom: $request->getKingdom(),
            equipment_id: $request->getEquipmentId(),
            faction_id: $request->getFactionId(),
            id: $oldcharacter->getId()
        );

        return $this->repository->save($updatedcharacter);
    }
}
