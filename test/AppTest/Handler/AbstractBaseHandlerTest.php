<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\AbstractBaseHandler;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use PHPUnit\Framework\TestCase;

class DummyHandler extends AbstractBaseHandler
{
    public function handle(
        \Psr\Http\Message\ServerRequestInterface $request
    ): \Psr\Http\Message\ResponseInterface {
        throw new \RuntimeException('not used in test');
    }
}

class AbstractBaseHandlerTest extends TestCase
{
    public function testFlashMessagesRetrievedFromRequestAttribute(): void
    {
        $flash = $this->createMock(FlashMessagesInterface::class);

        $request = (new ServerRequest())->withAttribute(
            FlashMessageMiddleware::FLASH_ATTRIBUTE,
            $flash
        );

        $handler = new DummyHandler();

        $result = $handler->flashMessages($request);

        $this->assertSame($flash, $result);
    }
}
