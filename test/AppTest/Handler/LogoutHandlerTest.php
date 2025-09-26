<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\Auth\Def\LogoutHandler;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\Session;
use PHPUnit\Framework\TestCase;

class LogoutHandlerTest extends TestCase
{
    public function testHandleClearsSessionAndRedirects(): void
    {
        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('clear');

        $request = new ServerRequest();
        $request = $request->withAttribute('session', $session);

        $handler = new LogoutHandler();

        $response = $handler->handle($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/', $response->getHeaderLine('Location'));
    }
}
