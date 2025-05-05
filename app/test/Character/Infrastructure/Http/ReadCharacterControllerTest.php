<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Application\ReadCharacterUseCase;
use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\CharacterToArrayTransformer;
use App\Character\Infrastructure\Http\ReadCharacterController;
use App\Test\Shared\BaseTestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Slim\Psr7\Response;

class ReadCharacterControllerTest extends BaseTestCase
{
    #[Test]
    #[Group('happy-path')]
    #[Group('acceptance')]
    #[Group('readCharacter')]
    public function givenARepositoryWithOneCharacterIdWhenReadCharacterThenReturnCharacterAsJson(): void
    {
        $app = $this->getAppInstanceWithoutCache();
        $repository = $app->getContainer()->get(CharacterRepository::class);

        $expectedCharacter = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Doe',
            1,
            1
        );
        $expectedCharacter = $repository->save($expectedCharacter);

        $request = $this->createRequest('GET', '/character/' . $expectedCharacter->getId());
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $serializedPayload = json_encode([
            'character' => CharacterToArrayTransformer::transform($expectedCharacter),
        ]);

        $this->assertEquals($serializedPayload, $payload);
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('readCharacter')]
    public function givenUseCaseThrowsExceptionWhenReadCharacterThenReturn500(): void
    {
        // Arrange: mock del caso de uso que lanza excepción
        /** @var ReadCharacterUseCase&\PHPUnit\Framework\MockObject\MockObject $mockUseCase */
        $mockUseCase = $this->createMock(ReadCharacterUseCase::class);
        $mockUseCase->method('execute')->willThrowException(new \RuntimeException('Unexpected failure'));

        // Instanciar el controlador con el mock
        $controller = new ReadCharacterController($mockUseCase);

        // Crear request y response
        $request = $this->createRequest('GET', '/character/999');
        $response = new Response();

        // Act: ejecutar el controlador con un id ficticio
        $result = $controller($request, $response, ['id' => 999]);

        // Assert: comprobar que devuelve 500 y el mensaje esperado
        $this->assertEquals(500, $result->getStatusCode());

        $payload = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('error', $payload);
        $this->assertEquals('Failed to read character', $payload['error']);
        $this->assertArrayHasKey('message', $payload);
        $this->assertStringContainsString('Unexpected failure', $payload['message']);
    }
}
