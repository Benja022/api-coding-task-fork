<?php

namespace App\Test\Equipment\Application;

use App\Equipment\Application\ReadEquipmentUseCase;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\Exception\EquipmentNotFoundException;
use App\Test\Equipment\Application\MotherObject\ReadEquipmentUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class ReadEquipmentUseCaseTest extends BaseTestCase
{
    private $repository;
    private $useCase;

    protected function setUp(): void
    {
        $equipment = ReadEquipmentUseCaseRequestMotherObject::valid();
        $equipmentId = 1;

        /** @var EquipmentRepository&\PHPUnit\Framework\MockObject\MockObject $repository */
        $repository = $this->createMock(EquipmentRepository::class);
        $repository->method('find')
            ->with($equipmentId)
            ->willReturn($equipment);

        $this->repository = $repository;
        $this->useCase = new ReadEquipmentUseCase($this->repository);
    }

    #[Test]
    #[Group('happy-path')]
    #[Group('unit')]
    #[Group('readEquipment')]
    #[DataProvider('provideEquipment')]
    public function givenARepositoryWithOneEquipmentIdWhenReadCharacterThenReturnEquipment(): void
    {
        $equipment = ReadEquipmentUseCaseRequestMotherObject::valid();
        $equipmentId = 1; // ID fijo para la prueba

        $result = $this->useCase->execute($equipmentId);

        $this->assertEquals($equipment->getName(), $result->getName());
        $this->assertEquals($equipment->getType(), $result->getType());
        $this->assertEquals($equipment->getMadeBy(), $result->getMadeBy());
    }

    public static function provideEquipment(): array
    {
        return [
            [ReadEquipmentUseCaseRequestMotherObject::valid()],
        ];
    }
}
