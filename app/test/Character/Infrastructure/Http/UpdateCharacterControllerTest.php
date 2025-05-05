<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\UpdateCharacterUseCase;
use App\Character\Application\UpdateCharacterUseCaseRequest;
use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;
use App\Test\Character\Application\MotherObject\UpdateCharacterUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use Slim\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

/**
 * @test
 * @testdox UpdateCharacterControllerTest
 * @covers \App\Character\Infrastructure\Http\UpdateCharacterController
 */

class UpdateCharacterControllerTest extends BaseTestCase
{
    #[Test]
    #[Group('unit')]
    #[Group('updateCharacter')]
    public function testSuccesfullUpdateReturns200(): void
    {
        $character = $this->createMock(Character::class);
        /** @var \App\Character\Application\UpdateCharacterUseCase&\PHPUnit\Framework\MockObject\MockObject $useCaseMock */
        $useCaseMock = $this->createMock(UpdateCharacterUseCase::class);
        $useCaseMock->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(UpdateCharacterUseCaseRequest::class))
            ->willReturn($character);


        $controller = new UpdateCharacterController($useCaseMock);

        $data = [
            'name' => 'John Doe',
            'birth_date' => '1990-01-01',
            'kingdom' => 'Kingdom of Spain',
            'equipment_id' => 1,
            'faction_id' => 1
        ];

        $request = $this->createJsonRequest(
            'PUT',
            '/character/1',
            $data
        );

        $response = new Response();

        $result = $controller($request, $response, ['id' => 1]);

        $this->assertEquals(200, $result->getStatusCode());

        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('character', $body);
        $this->assertEquals('Character updated correctly', $body['message']);
    }

    #[Test]
    #[Group('unit')]
    #[Group('updateCharacter')]
    public function testFailedUpdateReturns400(): void
    {
        /** @var \App\Character\Application\UpdateCharacterUseCase&\PHPUnit\Framework\MockObject\MockObject $useCaseMock */
        $useCaseMock = $this->createMock(UpdateCharacterUseCase::class);
        $controller = new UpdateCharacterController($useCaseMock);

        $request = $this->createJsonRequest(
            'PUT',
            '/character/1',
        );
        $request->getBody()->write('{"invalid:"'); // JSON invalid
        $request->getBody()->rewind();

        $response = new Response();

        $result = $controller($request, $response, ['id' => 1]);

        $this->assertEquals(400, $result->getStatusCode());

        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Invalid JSON', $body['error']);
    }

    #[Test]
    #[Group('unit')]
    #[Group('updateCharacter')]
    public function testMissingFieldReturns400(): void
    {
        /** @var \App\Character\Application\UpdateCharacterUseCase&\PHPUnit\Framework\MockObject\MockObject $useCaseMock */
        $useCaseMock = $this->createMock(UpdateCharacterUseCase::class);
        $controller = new UpdateCharacterController($useCaseMock);

        $data = [
            'name' => 'John Doe',
            'birth_date' => '1990-01-01',
            'kingdom' => 'Kingdom of Spain',
            'equipment_id' => 1,
        ];

        $request = $this->createJsonRequest(
            'PUT',
            '/character/1', // URL
            $data
        );

        $response = new Response();

        $result = $controller($request, $response, ['id' => 1]);

        $this->assertEquals(400, $result->getStatusCode());

        $body = json_decode((string) $result->getBody(), true);
        $this->assertEquals('Missing required fields', $body['error']);
    }

    #[Test]
    #[Group('unit')]
    #[Group('updateCharacter')]
    public function testUseCaseThrowsExceptionReturns500(): void
    {
        /** @var \App\Character\Application\UpdateCharacterUseCase&\PHPUnit\Framework\MockObject\MockObject $useCaseMock */
        $useCaseMock = $this->createMock(UpdateCharacterUseCase::class);
        $useCaseMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Unexpected error'));

        $controller = new UpdateCharacterController($useCaseMock);

        $data = [
            'name' => 'John Doe',
            'birth_date' => '1990-01-01',
            'kingdom' => 'Kingdom of Spain',
            'equipment_id' => 1,
            'faction_id' => 1
        ];

        $request = $this->createJsonRequest(
            'PUT',
            '/character/1',
            $data
        );

        $response = new Response();

        $result = $controller($request, $response, ['id' => 1]);

        $this->assertEquals(500, $result->getStatusCode());

        $body = json_decode((string) $result->getBody(), true);
        $this->assertEquals('Unexpected error', $body['message']);
    }

    #[Test]
    #[Group('unit')]
    #[Group('updateCharacter')]
    public function testThrowCharacterNotFoundExceptionIfCharacterDoesNotExist(): void
    {
        /** @var CharacterRepository&\PHPUnit\Framework\MockObject\MockObject $repository */
        $repository = $this->createMock(CharacterRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willThrowException(new CharacterNotFoundException('Character not found'));

        $useCase = new UpdateCharacterUseCase($repository);

        $request = UpdateCharacterUseCaseRequestMotherObject::valid();

        $this->expectException(CharacterNotFoundException::class);
        $this->expectExceptionMessage('Character not found');

        $useCase->execute($request);
    }
}
