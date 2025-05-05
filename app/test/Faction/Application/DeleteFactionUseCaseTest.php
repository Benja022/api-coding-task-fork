<?php

namespace App\Test\Faction\Application;

use App\Faction\Application\DeleteFactionUseCase;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Persistence\InMemory\ArrayFactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException;
use App\Test\Faction\Application\MotherObject\DeleteFactionUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class DeleteFactionUseCaseTest extends BaseTestCase
{
    private $repository;
    private $useCase;

    protected function setUp(): void
    {
        $faction = DeleteFactionUseCaseRequestMotherObject::valid();
        /** @var FactionRepository&\PHPUnit\Framework\MockObject\MockObject $repository */
        $repository = $this->mockRepositoryWithFind(FactionRepository::class, [$faction]);
        // @phpstan-ignore-next-line
        $this->repository = $repository;
        $this->useCase = new DeleteFactionUseCase($this->repository);
    }

    #[Test]
    #[Group('happy-path')]
    #[Group('unit')]
    #[Group('deleteFaction')]
    #[DataProvider('provideFactions')]
    public function givenAValidFactionWhenDeleteThenReturnTrue()
    {
        $faction = DeleteFactionUseCaseRequestMotherObject::valid();
        $repository = new ArrayFactionRepository();
        $faction = $repository->save($faction);

        $sut = new DeleteFactionUseCase($repository);
        $sut->execute($faction->getId());

        // Verificamos que ya no existe
        $this->expectException(FactionNotFoundException::class);
        $repository->find($faction->getId());
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('deleteFaction')]
    // #[DataProvider('provideFactions')]
    public function givenAnInvalidFactionWhenDeleteThenReturnFalse()
    {
        $faction = DeleteFactionUseCaseRequestMotherObject::invalidId();
        $repository = new ArrayFactionRepository();
        $faction = $repository->save($faction);

        $sut = new DeleteFactionUseCase($repository);

        $sut->execute($faction->getId());

        // Verificamos que ya no existe
        $this->expectException(FactionNotFoundException::class);
        $this->expectExceptionMessage('Faction not found');

        $sut->execute($faction->getId());
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('deleteFaction')]
    public function givenANonExistentFactionWhenDeleteThenThrowException()
    {
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $this->expectException(\App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException::class);
        $this->expectExceptionMessage('Faction not found');

        $this->useCase->execute('999');
    }

    public static function provideFactions(): array
    {
        return [
            'valid faction' => [
                DeleteFactionUseCaseRequestMotherObject::valid()
            ],
            'invalid id' => [
                DeleteFactionUseCaseRequestMotherObject::invalidId()
            ],
        ];
    }
}
