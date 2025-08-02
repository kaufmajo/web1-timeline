<?php

declare(strict_types=1);

namespace App\Handler\Termin\Def;

use App\Handler\Termin\AbstractTerminHandler;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TerminShowHandler extends AbstractTerminHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // param
        $terminIdParam = (int) $request->getAttribute('p1');

        // init
        $terminRepository = $this->getTerminRepository();

        // fetch termin
        $terminEntity = $terminRepository->findTerminById($terminIdParam);

        // view
        $viewData = [
            'terminEntity' => $terminEntity,
            'back' => $request->getQueryParams()['back'] ?? '/',
        ];

        // send response to client
        return new HtmlResponse($this->templateRenderer->render('app::termin/def/show', $viewData), 200);
    }
}
