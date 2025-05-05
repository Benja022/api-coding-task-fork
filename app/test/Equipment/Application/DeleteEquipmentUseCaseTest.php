<?php

namespace App\Test\Equipment\Application;

use App\Equipment\Application\DeleteEquipmentUseCase;
use App\Equipment\Domain\EquipmentRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Test\Shared\BaseTestCase;
use App\Equipment\Infrastructure\Persistence\InMemory\ArrayEquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\Exception\EquipmentNotFoundException;
use App\Test\Equipment\Application\MotherObject\DeleteEquipmentUseCaseRequestMotherObject;

class DeleteEquipmentUseCaseTest extends BaseTestCase
{

    private $repository;
    private $useCase;

    protected function setUp(): void
    {
        $equipment = DeleteEquipmentUseCaseRequestMotherObject::valid();
        /** @var EquipmentRepository&\PHPUnit\Framework\MockObject\MockObject $repository */
        $repository = $this->mockRepositoryWithFind(EquipmentRepository::class, [$equipment]);
        // @phpstan-ignore-next-line
        $this->repository = $repository;
        $this->useCase = new DeleteEquipmentUseCase($this->repository);
    }

    #[Test]
    #[Group('happy-path')]
    #[Group('unit')]
    #[Group('deleteEquipment')]
    #[DataProvider('provideEquipment')]

    public function givenAValidEquipmentWhenDeleteThenReturnTrue()
    {
        $equipment = DeleteEquipmentUseCaseRequestMotherObject::valid();
        $repository = new ArrayEquipmentRepository();
        $equipment = $repository->save($equipment);

        $sut = new DeleteEquipmentUseCase($repository);
        $sut->execute($equipment->getId());

        // Verificamos que ya no existe
        $this->expectException(EquipmentNotFoundException::class);
        $repository->find($equipment->getId());
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('deleteEquipment')]
    #[DataProvider('provideEquipment')]
    public function givenAnInvalidEquipmentWhenDeleteThenReturnFalse()
    {
        $equipment = DeleteEquipmentUseCaseRequestMotherObject::invalidId();

        $sut = new DeleteEquipmentUseCase($this->repository);

        $this->expectException(EquipmentNotFoundException::class);
        $this->expectExceptionMessage('Equipment not found');

        $sut->execute($equipment->getId());
    }

    public static function provideEquipment(): array
    {
        return [
            [DeleteEquipmentUseCaseRequestMotherObject::valid()],
            [DeleteEquipmentUseCaseRequestMotherObject::invalidId()],
        ];
    }
}
