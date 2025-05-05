<?php

namespace App\Test\Faction\Application;

use App\Faction\Application\ReadFactionUseCase;
use App\Faction\Domain\FactionRepository;
use App\Test\Faction\Application\MotherObject\ReadFactionUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ReadFactionUseCaseTest extends BaseTestCase
{
    private $repository;
    private $useCase;

    protected function setUp(): void
    {
        $faction = ReadFactionUseCaseRequestMotherObject::valid();
        $factionId = $faction->getId();

        /** @var FactionRepository&\PHPUnit\Framework\MockObject\MockObject $repository */
        $repository = $this->createMock(FactionRepository::class);
        $repository->method('find')
            ->with($factionId)
            ->willReturn($faction);

        $this->repository = $repository;
        $this->useCase = new ReadFactionUseCase($this->repository);
    }

    #[Test]
    #[Group('happy-path')]
    #[Group('unit')]
    #[Group('readFaction')]
    public function givenARepositoryWithOneFactionIdWhenReadFactionThenReturnFaction(): void
    {
        $request = ReadFactionUseCaseRequestMotherObject::valid();
        $result = $this->useCase->execute($request->getId());

        $this->assertEquals($request, $result);
    }

    public static function provideFaction(): array
    {
        return [
            [ReadFactionUseCaseRequestMotherObject::valid()],
        ];
    }
}
