<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\Auth\Def\LoginHandler;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Csrf\CsrfGuardInterface;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Session\SessionInterface;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Authentication\Session\PhpSession;
use PHPUnit\Framework\TestCase;

class LoginHandlerTest extends TestCase
{
    public function testGetDisplaysLoginFormAndStoresRedirectInSession(): void
    {
        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer->expects($this->once())
            ->method('render')
            ->with('app::auth/def/login', $this->callback(fn($args) => isset($args['__csrf'])))
            ->willReturn('<form></form>');

        $guard = $this->createMock(CsrfGuardInterface::class);
        $guard->expects($this->once())->method('generateToken')->willReturn('token123');

        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())
            ->method('set')
            ->with('authentication:redirect', $this->callback(function ($v) {
                return is_string($v) || $v instanceof \Laminas\Diactoros\Uri;
            }));

        $adapter = $this->createMock(PhpSession::class);

        $request = (new ServerRequest())
            ->withAttribute(CsrfMiddleware::GUARD_ATTRIBUTE, $guard)
            ->withAttribute('session', $session)
            ->withHeader('Referer', '/previous');

        $handler = new LoginHandler($adapter);
        $handler->setTemplateRenderer($renderer);

        $response = $handler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertStringContainsString('<form', (string) $response->getBody());
    }
}
