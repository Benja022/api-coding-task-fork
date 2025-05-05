<?php

namespace App\Test\Character\Application;

use App\Character\Application\UpdateCharacterUseCase;
use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\InMemory\ArrayCharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;
use App\Test\Character\Application\MotherObject\UpdateCharacterUseCaseRequestMotherObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;

class UpdateCharacterUseCaseTest extends TestCase
{
    #[Test]
    #[Group('happy-path')]
    #[Group('unit')]
    #[Group('updateCharacter')]
    public function givenARequestWithValidDataWhenUpdateCharacterThenReturnSuccess()
    {
        $request = UpdateCharacterUseCaseRequestMotherObject::valid();

        // Crear un personaje existente en el repositorio
        $existingCharacter = new Character(
            'Original Name',
            '1980-01-01',
            'Original Kingdom',
            1,
            1
        );

        // Crear el repositorio y guardar el personaje existente
        $repository = new ArrayCharacterRepository();
        $existingCharacter = $repository->save($existingCharacter);

        // Crear el caso de uso con el repositorio que contiene el personaje
        $sut = new UpdateCharacterUseCase($repository);

        $result = $sut->execute($request);

        $this->assertEquals($existingCharacter->getId(), $result->getId());
        $this->assertEquals('John Doe', $result->getName());
        $this->assertEquals('1990-01-01', $result->getBirthDate());
        $this->assertEquals('Kingdom of Spain', $result->getKingdom());
        $this->assertEquals(1, $result->getEquipmentId());
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('updateCharacter')]
    public function givenARequestWithNonExistentCharacterWhenUpdateCharacterThenThrowException()
    {
        $request = UpdateCharacterUseCaseRequestMotherObject::valid();

        /** @var CharacterRepository&MockObject $repository */
        $repository = $this->createMock(CharacterRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with($request->getId())
            ->willReturn(null);

        // Crear el caso de uso con el repositorio mock
        $sut = new UpdateCharacterUseCase($repository);

        $this->expectException(CharacterNotFoundException::class);
        $this->expectExceptionMessage('Character not found');

        $sut->execute($request);
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
