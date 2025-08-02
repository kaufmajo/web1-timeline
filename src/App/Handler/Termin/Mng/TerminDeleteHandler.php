<?php

declare(strict_types=1);

namespace App\Handler\Termin\Mng;

use App\Handler\Termin\AbstractTerminHandler;
use App\Traits\Aware\FormStorageAwareTrait;
use App\Traits\Aware\TerminCommandAwareTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TerminDeleteHandler extends AbstractTerminHandler
{
    use FormStorageAwareTrait;

    use TerminCommandAwareTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // init
        $terminIdParam     = (int)$request->getAttribute('p1');
        $terminEntity  = $this->getTerminRepository()->findTerminById($terminIdParam);
        $terminCommand = $this->getTerminCommand();

        // view
        $viewData = [
            'myInitConfig' => $this->getMyInitConfig(),
            'terminEntity' => $terminEntity,
        ];

        // ask for confirmation
        if ('POST' !== $request->getMethod()) {
            return new HtmlResponse($this->templateRenderer->render('app::termin/mng/delete', $viewData));
        }

        // redirect if confirmation is not given
        if (!isset($request->getParsedBody()['confirm']) || 'Löschen' !== $request->getParsedBody()['confirm']) {
            return new RedirectResponse($this->getUrlpoolService()->get(fragment: $terminEntity->getTerminDatumStart()));
        }

        // ok ... now execute delete
        $terminCommand->deleteTermin($terminEntity);

        $this->flashMessages($request)->flash('secondary', 'default');

        return new RedirectResponse($this->getUrlpoolService()->get(query_params: ['date' => $terminEntity->getTerminDatumStart()], fragment:$terminEntity->getTerminDatumStart()));
    }
}
