<?php

namespace App\Test\Equipment\Application;

use App\Equipment\Application\UpdateEquipmentUseCase;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\Persistence\InMemory\ArrayEquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\Exception\EquipmentNotFoundException;
use App\Test\Equipment\Application\MotherObject\ReadEquipmentUseCaseRequestMotherObject;
use App\Test\Equipment\Application\MotherObject\UpdateEquipmentUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;

class UpdateEquipmentUseCaseTest extends BaseTestCase
{
    private $repository;
    private $useCase;

    protected function setUp(): void
    {
        $equipment = ReadEquipmentUseCaseRequestMotherObject::valid();
        /** @var EquipmentRepository&\PHPUnit\Framework\MockObject\MockObject $repository */
        $repository = $this->mockRepositoryWithFind(EquipmentRepository::class, [$equipment]);
        // @phpstan-ignore-next-line
        $this->repository = $repository;
        $this->useCase = new UpdateEquipmentUseCase($this->repository);
    }

    #[Test]
    #[Group('happy-path')]
    #[Group('unit')]
    #[Group('updateEquipment')]
    #[DataProvider('provideEquipment')]
    public function givenARequestWithValidDataWhenUpdateEquipmentThenReturnSuccess()
    {
        $request = UpdateEquipmentUseCaseRequestMotherObject::valid();
        $repository = new ArrayEquipmentRepository();

        $existingEquipment = ReadEquipmentUseCaseRequestMotherObject::valid();
        $existingEquipment = $repository->save($existingEquipment);
        $sut = new UpdateEquipmentUseCase($repository);
        $result = $sut->execute($request);
        $this->assertEquals($existingEquipment->getId(), $result->getId());
        $this->assertEquals('Anduril', $result->getName());
        $this->assertEquals('Weapon', $result->getType());
        $this->assertEquals('Elfs', $result->getMadeBy());
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('updateEquipment')]
    public function givenARequestWithInvalidDataWhenUpdateEquipmentThenThrowException()
    {
        $request = UpdateEquipmentUseCaseRequestMotherObject::invalid();
        $this->expectException(EquipmentNotFoundException::class);
        $this->expectExceptionMessage('Equipment not found');
        $this->useCase->execute($request);
    }

    public static function provideEquipment(): array
    {
        return [
            [UpdateEquipmentUseCaseRequestMotherObject::valid()],
            [UpdateEquipmentUseCaseRequestMotherObject::invalid()],
        ];
    }
}
