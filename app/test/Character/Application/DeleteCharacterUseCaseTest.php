<?php

namespace App\Test\Character\Application;

use App\Character\Application\DeleteCharacterUseCase;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\InMemory\ArrayCharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;
use PHPUnit\Framework\TestCase;
use App\Character\Domain\CharacterFactory;

class DeleteCharacterUseCaseTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group unit
     * @group deleteCharacter
     */
    public function givenAValidCharacterWhenDeleteThenReturnTrue()
    {
        // Primero creamos un personaje
        $character = CharacterFactory::build(
            'Test Character',
            '1990-01-01',
            'Test Kingdom',
            1,
            1,
            1 // Asignamos un ID explícito
        );
        $repository = $this->mockCharacterRepository([$character]);

        $sut = new DeleteCharacterUseCase($repository);
        $sut->execute($character->getId());

        // Verificamos que ya no existe
        $this->expectException(CharacterNotFoundException::class);
        $repository->find($character->getId());
    }

    /**
     * @test
     * @group unhappy-path
     * @group unit
     * @group deleteCharacter
     */
    public function givenAnInvalidCharacterWhenDeleteThenExceptionShouldBeThrown()
    {
        $repository = $this->mockCharacterRepository([]);
        $sut = new DeleteCharacterUseCase($repository);

        $this->expectException(CharacterNotFoundException::class);
        $this->expectExceptionMessage('Character not found');

        $sut->execute(999999); // ID que no existe
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
