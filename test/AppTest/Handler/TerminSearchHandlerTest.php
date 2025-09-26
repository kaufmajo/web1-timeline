<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\Termin\Mng\TerminSearchHandler;
use App\Model\Termin\TerminCollection;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Form\FormInterface;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;

class TerminSearchHandlerTest extends TestCase
{
    public function testHandleReturnsRenderedHtmlWhenNoQueryParamsOrInvalidForm(): void
    {
        // repository mock
        $terminRepository = $this->createMock(\App\Model\Termin\TerminRepositoryInterface::class);
        $terminRepository->method('fetchMitvon')->willReturn([['A']]);
        $terminRepository->method('fetchKategorie')->willReturn([['B']]);
        $terminRepository->method('fetchBetreff')->willReturn([['C']]);

        // form mock - invalid
        $form = $this->createMock(FormInterface::class);
        $form->method('isValid')->willReturn(false);
        $form->method('getData')->willReturn([]);

        // template renderer
        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer->expects($this->once())
            ->method('render')
            ->with('app::termin/mng/search', $this->isType('array'))
            ->willReturn('html');

    $request = new ServerRequest();

    $handler = new TerminSearchHandler();
    // inject mocks
    $handler->setTerminRepository($terminRepository);
    $handler->setTemplateRenderer($renderer);
    // store form used by getTerminSearchForm
    $handler->setForm('def-termin-search-form', $form);
    // urlpool service is used via ->save()
    $urlpool = $this->createMock(\App\Service\UrlpoolService::class);
    $urlpool->expects($this->once())->method('save');
    $handler->setUrlpoolService($urlpool);

        $response = $handler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertStringContainsString('html', (string)$response->getBody());
    }

    public function testHandleFetchesTerminWhenQueryParamsAndValidForm(): void
    {
        // set $_GET so handler treats request as search
        $_GET['q'] = '1';

        $terminRepository = $this->createMock(\App\Model\Termin\TerminRepositoryInterface::class);

        $terminRepository->method('fetchMitvon')->willReturn([['A']]);
        $terminRepository->method('fetchKategorie')->willReturn([['B']]);
        $terminRepository->method('fetchBetreff')->willReturn([['C']]);
        $terminRepository->expects($this->once())->method('fetchTermin')->willReturn([]);

        $form = $this->createMock(FormInterface::class);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn(['search_suchtext' => 'foo']);

        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer->expects($this->once())
            ->method('render')
            ->with('app::termin/mng/search', $this->isType('array'))
            ->willReturn('html');

    $request = (new ServerRequest())->withQueryParams(['search_suchtext' => 'foo']);

    $handler = new TerminSearchHandler();
    $handler->setTerminRepository($terminRepository);
    $handler->setTemplateRenderer($renderer);
    $handler->setForm('def-termin-search-form', $form);
    $urlpool = $this->createMock(\App\Service\UrlpoolService::class);
    $urlpool->expects($this->once())->method('save');
    $handler->setUrlpoolService($urlpool);

        $response = $handler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertStringContainsString('html', (string)$response->getBody());

        // cleanup
        unset($_GET['q']);
    }
}
