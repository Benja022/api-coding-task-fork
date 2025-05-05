<?php

namespace App\Test\Equipment\Application;

use App\Equipment\Application\CreateEquipmentUseCase;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\Exception\EquipmentValidationException;
use App\Equipment\Infrastructure\Persistence\InMemory\ArrayEquipmentRepository;
use App\Test\Equipment\Application\MotherObject\CreateEquipmentUseCaseRequestMotherObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

class CreateEquipmentUseCaseTest extends TestCase
{

    #[Test]
    #[Group('happy-path')]
    #[Group('unit')]
    #[Group('createEquipment')]
    public function givenARequestWithValidDataWhenCreateEquipmentThenReturnSuccess()
    {
        $request = CreateEquipmentUseCaseRequestMotherObject::valid();
        $sut = new CreateEquipmentUseCase(
            $this->mockEquipmentRepository([])
        );

        $result = $sut->execute($request);

        $this->assertEquals(1, $result->getId());
        $this->assertEquals('Anduril', $result->getName());
        $this->assertEquals('Weapon', $result->getType());
        $this->assertEquals('Elfs', $result->getMadeBy());
    }

    private function mockEquipmentRepository(array $equipments): EquipmentRepository
    {
        $repository = new ArrayEquipmentRepository();

        foreach ($equipments as $equipment) {
            $repository->save($equipment);
        }

        return $repository;
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('createEquipment')]
    #[DataProvider('invalidDataProvider')]
    public function givenARequestWithInvalidDataWhenCreateEquipmentThenReturnError($request, $expectedExceptcion)
    {
        $sut = new CreateEquipmentUseCase(
            $this->mockEquipmentRepository([])
        );

        $this->expectException(EquipmentValidationException::class);
        $this->expectExceptionMessage($expectedExceptcion->getMessage());
        $sut->execute($request);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'invalid name' => [CreateEquipmentUseCaseRequestMotherObject::withInvalidName(), EquipmentValidationException::nameRequired()],
            'invalid type' => [CreateEquipmentUseCaseRequestMotherObject::withInvalidType(), EquipmentValidationException::typeRequired()],
            'invalid made by' => [CreateEquipmentUseCaseRequestMotherObject::withInvalidMadeBy(), EquipmentValidationException::madeByRequired()],
        ];
    }
}
