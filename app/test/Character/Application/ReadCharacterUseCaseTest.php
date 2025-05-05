<?php

namespace App\Test\Character\Application;

use App\Character\Application\ReadCharacterUseCase;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\InMemory\ArrayCharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;
use PHPUnit\Framework\TestCase;
use App\Test\Character\Application\MotherObject\ReadCharacterUseCaseRequestMotherObject;

class ReadCharacterUseCaseTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group unit
     * @group readCharacter
     */
    public function givenARepositoryWithOneCharacterIdWhenReadCharacterThenReturnCharacter()
    {
        $character = ReadCharacterUseCaseRequestMotherObject::valid();
        $repository = $this->mockCharacterRepository([$character]);
        $sut = new ReadCharacterUseCase($repository);

        $result = $sut->execute($character->getId());

        $this->assertEquals($character->getId(), $result->getId());
        $this->assertEquals($character->getName(), $result->getName());
        $this->assertEquals($character->getBirthDate(), $result->getBirthDate());
        $this->assertEquals($character->getKingdom(), $result->getKingdom());
        $this->assertEquals($character->getEquipmentId(), $result->getEquipmentId());
        $this->assertEquals($character->getFactionId(), $result->getFactionId());
    }

    /**
     * @test
     * @group unhappy-path
     * @group unit
     * @group readCharacter
     */
    public function givenARepositoryWithNonExistingCharacterIdWhenReadCharacterThenExceptionShouldBeRaised()
    {
        $repository = $this->mockCharacterRepository([]);
        $sut = new ReadCharacterUseCase($repository);

        $this->expectException(CharacterNotFoundException::class);
        $this->expectExceptionMessage('Character not found');

        $sut->execute(999999);
    }

    private function mockCharacterRepository(array $characters): CharacterRepository
    {
        $repository = new ArrayCharacterRepository();
        foreach ($characters as $character) {
            $repository->save($character);
        }
        return $repository;
    }
}
