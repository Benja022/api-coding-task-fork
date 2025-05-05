<?php

namespace App\Test\Faction\Application;

use App\Faction\Application\UpdateFactionUseCase;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Persistence\InMemory\ArrayFactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException;
use App\Test\Faction\Application\MotherObject\ReadFactionUseCaseRequestMotherObject;
use App\Test\Faction\Application\MotherObject\UpdateFactionUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class UpdateFactionUseCaseTest extends BaseTestCase
{
    private $repository;
    private $useCase;

    protected function setUp(): void
    {
        $faction = UpdateFactionUseCaseRequestMotherObject::valid();
        /** @var FactionRepository&\PHPUnit\Framework\MockObject\MockObject $repository */
        $repository = $this->mockRepositoryWithFind(FactionRepository::class, [$faction]);
        // @phpstan-ignore-next-line
        $this->repository = $repository;
        $this->useCase = new UpdateFactionUseCase($this->repository);
    }

    #[Test]
    #[Group('happy-path')]
    #[Group('unit')]
    #[Group('updateFaction')]
    public function givenARequestWithValidDataWhenUpdateFactionThenReturnSuccess()
    {
        $request = UpdateFactionUseCaseRequestMotherObject::valid();
        $repository = new ArrayFactionRepository();

        $existingFaction = ReadFactionUseCaseRequestMotherObject::valid();
        $existingFaction = $repository->save($existingFaction);
        $sut = new UpdateFactionUseCase($repository);
        $result = $sut->execute($request);
        $this->assertEquals($existingFaction->getId(), $result->getId());
        $this->assertEquals('Elfs', $result->getFactionName());
        $this->assertEquals('Elfs are a race of people who live in the forest and are known for their agility and speed.', $result->getDescription());
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('updateFaction')]
    public function givenARequestWithInvalidDataWhenUpdateFactionThenThrowException()
    {
        $request = UpdateFactionUseCaseRequestMotherObject::invalid();
        $repository = new ArrayFactionRepository();
        $existingFaction = ReadFactionUseCaseRequestMotherObject::valid();
        $existingFaction = $repository->save($existingFaction);
        $this->expectException(FactionNotFoundException::class);
        $this->expectExceptionMessage('Faction not found');
        $sut = new UpdateFactionUseCase($repository);
        $sut->execute($request);
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('updateFaction')]
    public function givenANonExistentFactionWhenUpdateThenThrowException()
    {
        // Configure the repository to return null when find is called
        /** @var FactionRepository&\PHPUnit\Framework\MockObject\MockObject $repository */
        $repository = $this->mockRepositoryWithFind(FactionRepository::class, []);
        $repository->expects($this->once())
            ->method('find')
            ->willReturn(null);

        // Create new petition with invalid data
        $request = UpdateFactionUseCaseRequestMotherObject::invalid();

        $sut = new UpdateFactionUseCase($repository);

        //Verify that the exception is thrown
        $this->expectException(FactionNotFoundException::class);
        $this->expectExceptionMessage('Faction not found');

        // Execute the use case
        $sut->execute($request);
    }
}
