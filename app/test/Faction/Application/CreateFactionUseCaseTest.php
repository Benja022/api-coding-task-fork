<?php

namespace App\Test\Faction\Application;

use App\Faction\Application\CreateFactionUseCase;
use App\Faction\Domain\Exception\FactionValidationException;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Persistence\InMemory\ArrayFactionRepository;
use App\Test\Faction\Application\MotherObject\CreateFactionUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class CreateFactionUseCaseTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    #[Group('happy-path')]
    #[Group('unit')]
    #[Group('createFaction')]
    public function givenARequestWithValidDataWhenCreateFactionThenReturnSuccess()
    {
        $request = CreateFactionUseCaseRequestMotherObject::valid();
        $sut = new CreateFactionUseCase(
            $this->mockFactionRepository([])
        );

        $result = $sut->execute($request);

        $this->assertEquals(1, $result->getId());
        $this->assertEquals('Rohirrim', $result->getFactionName());
        $this->assertEquals('Los rohirrim se caracterizaban por ser altos, fornidos y de tez pálida y cabellos rubios, con ojos azules o verdes en su mayoría.', $result->getDescription());
    }

    private function mockFactionRepository(array $factions): FactionRepository
    {
        $repository = new ArrayFactionRepository();

        foreach ($factions as $faction) {
            $repository->save($faction);
        }

        return $repository;
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('createFaction')]
    public function givenARequestWithInvalidDataWhenCreateFactionThenReturnError()
    {
        $request = CreateFactionUseCaseRequestMotherObject::invalid();
        $sut = new CreateFactionUseCase(
            $this->mockFactionRepository([])
        );

        $this->expectException(\App\Faction\Domain\Exception\FactionValidationException::class);
        $this->expectExceptionMessage('Faction name is required');

        $sut->execute($request);
    }

    public static function invalidDataProvider(): array
    {
        return [
            [CreateFactionUseCaseRequestMotherObject::invalid()],
        ];
    }
}
