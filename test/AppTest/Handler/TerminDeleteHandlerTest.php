<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\Termin\Mng\TerminDeleteHandler;
use App\Model\Termin\TerminEntity;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;

class TerminDeleteHandlerTest extends TestCase
{
    public function testGetRendersConfirmation(): void
    {
        $termin = new TerminEntity();
        $termin->setTerminId(1);
        $termin->setTerminDatumStart('2025-01-01');

        $repo = $this->createMock(\App\Model\Termin\TerminRepositoryInterface::class);
        $repo->method('findTerminById')->willReturn($termin);

        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer->expects($this->once())->method('render')->willReturn('confirm');

        $request = new ServerRequest();

        $handler = new TerminDeleteHandler();
        $handler->setTerminRepository($repo);
        $handler->setTemplateRenderer($renderer);
        // initialize required services that are typed properties
        $handler->setTerminCommand($this->createMock(\App\Model\Termin\TerminCommandInterface::class));
        $handler->setUrlpoolService($this->createMock(\App\Service\UrlpoolService::class));
        $handler->setConfig(['my_init_config' => []]);

        $response = $handler->handle($request->withAttribute('p1', 1));

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertStringContainsString('confirm', (string)$response->getBody());
    }

    public function testPostWithoutConfirmRedirects(): void
    {
        $termin = new TerminEntity();
        $termin->setTerminId(2);
        $termin->setTerminDatumStart('2025-02-02');

        $repo = $this->createMock(\App\Model\Termin\TerminRepositoryInterface::class);
        $repo->method('findTerminById')->willReturn($termin);

        $urlpool = $this->createMock(\App\Service\UrlpoolService::class);
        $urlpool->method('get')->willReturn('/manage');

        $request = (new ServerRequest())->withMethod('POST')->withParsedBody([]);

        $handler = new TerminDeleteHandler();
        $handler->setTerminRepository($repo);
        $handler->setUrlpoolService($urlpool);
        $handler->setTerminCommand($this->createMock(\App\Model\Termin\TerminCommandInterface::class));
        $handler->setConfig(['my_init_config' => []]);

        $response = $handler->handle($request->withAttribute('p1', 2));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/manage', $response->getHeaderLine('Location'));
    }

    public function testPostWithConfirmDeletesAndRedirects(): void
    {
        $termin = new TerminEntity();
        $termin->setTerminId(3);
        $termin->setTerminDatumStart('2025-03-03');

        $repo = $this->createMock(\App\Model\Termin\TerminRepositoryInterface::class);
        $repo->method('findTerminById')->willReturn($termin);

        $terminCommand = $this->createMock(\App\Model\Termin\TerminCommandInterface::class);
        $terminCommand->expects($this->once())->method('deleteTermin');

        $urlpool = $this->createMock(\App\Service\UrlpoolService::class);
        $urlpool->method('get')->willReturn('/manage');

        $flash = $this->createMock(\Mezzio\Flash\FlashMessagesInterface::class);
        $flash->expects($this->once())->method('flash')->with('secondary', 'default');

        $request = (new ServerRequest())
            ->withMethod('POST')
            ->withParsedBody(['confirm' => 'Löschen'])
            ->withAttribute('session', $this->createMock(\Mezzio\Session\SessionInterface::class));

        $handler = new TerminDeleteHandler();
        $handler->setTerminRepository($repo);
        $handler->setTerminCommand($terminCommand);
        $handler->setUrlpoolService($urlpool);
        $handler->setConfig(['my_init_config' => []]);

        // inject flashMessages via overriding property
        $handlerReflection = new \ReflectionObject($handler);
        $prop = $handlerReflection->getProperty('flashMessages');
        $prop->setAccessible(true);
        $prop->setValue($handler, $flash);

        $response = $handler->handle($request->withAttribute('p1', 3));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/manage', $response->getHeaderLine('Location'));
    }
}
