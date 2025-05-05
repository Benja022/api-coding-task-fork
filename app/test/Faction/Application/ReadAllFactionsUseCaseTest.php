<?php

namespace App\Test\Faction\Application;

use App\Faction\Application\ReadAllFactionsUseCase;
use App\Faction\Domain\FactionRepository;
use App\Test\Equipment\Application\MotherObject\ReadAllFactionsUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class ReadAllFactionsUseCaseTest extends BaseTestCase
{
    private ReadAllFactionsUseCase $useCase;
    private $repository;

    protected function setUp(): void
    {
        /** @var FactionRepository&\PHPUnit\Framework\MockObject\MockObject $repository */
        $repository = $this->createMock(FactionRepository::class);
        // @phpstan-ignore-next-line
        $this->repository = $repository;
        $this->useCase = new ReadAllFactionsUseCase($repository);
    }

    #[Test]
    #[Group('happy-path')]
    #[Group('unit')]
    #[Group('readAllFactions')]
    #[DataProvider('provideFactions')]
    public function testShouldReturnAllFactions(array $factions): void
    {
        $this->repository->expects($this->once())->method('findAll')->willReturn($factions);

        $result = $this->useCase->execute();

        $this->assertCount(count($factions), $result);
        foreach ($factions as $index => $faction) {
            $this->assertEquals($faction->getFactionName(), $result[$index]->getFactionName());
            $this->assertEquals($faction->getDescription(), $result[$index]->getDescription());
        }
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('readAllFactions')]
    #[DataProvider('provideFactions')]
    public function testShouldThrowExceptionIfRepositoryFails(array $factions): void
    {
        $this->repository->expects($this->once())->method('findAll')->willThrowException(new \Exception('Repository failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Repository failed');
        $this->useCase->execute();
    }

    public static function provideFactions(): array
    {
        return [
            'empty repository' => [
                ReadAllFactionsUseCaseRequestMotherObject::withEmptyRepository()
            ],
            'one faction' => [
                ReadAllFactionsUseCaseRequestMotherObject::valid()
            ],
            'multiple factions' => [
                ReadAllFactionsUseCaseRequestMotherObject::withMultipleFactions()
            ],
        ];
    }
}
