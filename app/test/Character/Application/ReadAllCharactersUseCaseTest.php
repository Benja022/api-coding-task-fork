<?php

namespace App\Test\Character\Application;

use App\Character\Application\ReadAllCharactersUseCase;
use App\Character\Domain\CharacterRepository;
use App\Test\Character\Application\MotherObject\ReadAllCharactersUseCaseRequestMotherObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ReadAllCharactersUseCaseTest extends TestCase
{
    /** @var CharacterRepository&MockObject */
    private CharacterRepository $repository;
    private ReadAllCharactersUseCase $useCase;

    protected function setUp(): void
    {
        /** @var CharacterRepository&MockObject $repository */
        $repository = $this->createMock(CharacterRepository::class);
        // @phpstan-ignore-next-line
        $this->repository = $repository;
        $this->useCase = new ReadAllCharactersUseCase($this->repository);
    }

    /**
     * @dataProvider provideCharacters
     */
    public function testShouldReturnAllCharacters(array $characters): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($characters);

        $result = $this->useCase->execute();

        $this->assertCount(count($characters), $result);
        foreach ($characters as $index => $character) {
            $this->assertEquals($character->getName(), $result[$index]->getName());
            $this->assertEquals($character->getBirthDate(), $result[$index]->getBirthDate());
            $this->assertEquals($character->getKingdom(), $result[$index]->getKingdom());
            $this->assertEquals($character->getEquipmentId(), $result[$index]->getEquipmentId());
            $this->assertEquals($character->getFactionId(), $result[$index]->getFactionId());
            $this->assertEquals($character->getId(), $result[$index]->getId());
        }
    }

    public static function provideCharacters(): array
    {
        return [
            'empty repository' => [
                ReadAllCharactersUseCaseRequestMotherObject::withEmptyRepository()
            ],
            'one character' => [
                ReadAllCharactersUseCaseRequestMotherObject::withOneCharacter()
            ],
            'multiple characters' => [
                ReadAllCharactersUseCaseRequestMotherObject::withMultipleCharacters()
            ],
            'three characters' => [
                ReadAllCharactersUseCaseRequestMotherObject::withThreeCharacters()
            ]
        ];
    }
}
