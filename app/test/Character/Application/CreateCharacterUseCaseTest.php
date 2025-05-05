<?php

namespace App\Test\Character\Application;

use App\Character\Application\CreateCharacterUseCase;
use App\Character\Application\CreateCharacterUseCaseRequest;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Exception\CharacterValidationException;
use App\Character\Infrastructure\Persistence\InMemory\ArrayCharacterRepository;
use App\Test\Character\Application\MotherObject\CreateCharacterUseCaseRequestMotherObject;
use DomainException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * This class is a test for the CreateCharacterUseCase.
 * It is used to create different scenarios for the use case.
 */

class CreateCharacterUseCaseTest extends TestCase
{
    #[Test]
    #[Group('happy-path')]
    #[Group('unit')]
    #[Group('createCharacter')]
    #[DataProvider('invalidDataProvider')]
    public function givenARequestWithValidDataWhenCreateCharacterThenReturnSuccess()
    {
        $request = CreateCharacterUseCaseRequestMotherObject::valid();
        $sut = new CreateCharacterUseCase(
            $this->mockCharacterRepository([])
        );

        $result = $sut->execute($request);

        $this->assertEquals(1, $result->getCharacter()->getId());
        $this->assertEquals('John Doe', $result->getCharacter()->getName());
        $this->assertEquals('1990-01-01', $result->getCharacter()->getBirthDate());
        $this->assertEquals('Kingdom of Spain', $result->getCharacter()->getKingdom());
        $this->assertEquals(1, $result->getCharacter()->getEquipmentId());
        $this->assertEquals(1, $result->getCharacter()->getFactionId());
    }

    private function mockCharacterRepository(array $characters): CharacterRepository
    {
        $repository = new ArrayCharacterRepository();

        foreach ($characters as $character) {
            $repository->save($character);
        }

        return $repository;
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('createCharacter')]
    #[DataProvider('invalidDataProvider')]
    public function givenARequestWithInvalidDataWhenCreateCharacterThenReturnError(
        CreateCharacterUseCaseRequest $request,
        DomainException $expectedException
    ) {
        $sut = new CreateCharacterUseCase(
            $this->mockCharacterRepository([])
        );

        $this->expectException($expectedException::class);
        $sut->execute($request);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'invalid name' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidName(),
                CharacterValidationException::nameRequired(),
            ],
            'invalid birth date' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidBirthDate(),
                CharacterValidationException::birthDateRequired()
            ],
            'invalid kingdom' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidKingdom(),
                CharacterValidationException::kingdomRequired()
            ],
            'invalid equipment ID' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidEquipmentId(),
                CharacterValidationException::equipmentIdRequired()
            ],
            'invalid faction ID' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidFactionId(),
                CharacterValidationException::factionIdRequired()
            ],
            'without birth date' => [
                CreateCharacterUseCaseRequestMotherObject::withoutBirthDate(),
                CharacterValidationException::birthDateRequired()
            ]
        ];
    }
}
