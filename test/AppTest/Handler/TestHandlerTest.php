<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\Home\Def\TestHandler;
use Doctrine\DBAL\Connection;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;

class TestHandlerTest extends TestCase
{
    public function testResponse(): void
    {
        // Arrange: DBAL-Mock vorbereiten
        $mockDbal = $this->createMock(Connection::class);
        $mockDbal->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT * FROM tajo1_termin')
            ->willReturn('test_result');

        // Handler instanziieren
        $handler = new TestHandler();

        // Trait-Methode `setDbalConnection` verwenden (aus DbalAwareTrait)
        $handler->setDbalConnection($mockDbal);

        // Request-Objekt (kann leer sein)
        $request = new ServerRequest();

        // Act: Handler aufrufen
        $response = $handler->handle($request);

        // Assert: Response prüfen
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('result', $data);
        $this->assertSame('test_result', $data['result']);
    }
}
