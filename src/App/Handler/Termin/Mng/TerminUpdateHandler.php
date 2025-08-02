<?php

declare(strict_types=1);

namespace App\Handler\Termin\Mng;

use App\Handler\Termin\AbstractTerminWriteHandler;
use App\Service\HelperService;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_merge_recursive;

class TerminUpdateHandler extends AbstractTerminWriteHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // script will stop when ...
        HelperService::isPostMaxSizeReached();

        // init
        $terminIdParam    = (int)$request->getAttribute('p1');
        $terminEntity = $this->getTerminRepository()->findTerminById($terminIdParam);

        // datalist data
        [$mitvonData, $kategorieData, $betreffData, $linkData, $linkTitelData, $imageData] = $this->datalistData();

        //form
        $terminForm = $this->getTerminForm();

        // view
        $viewData = [
            'myInitConfig' => $this->getMyInitConfig(),
            'terminEntity' => $terminEntity,
            'terminForm'   => $terminForm,
            'redirectUrl'  => $this->getUrlpoolService()->get(query_params: ['date' => $terminEntity->getTerminDatumStart()], fragment: $terminEntity->getTerminDatumStart()),
            'datalist'     => ['mitvon' => $mitvonData, 'kategorie' => $kategorieData, 'betreff' => $betreffData, 'link' => $linkData, 'link_titel' => $linkTitelData, 'image' => $imageData],
        ];

        if ('POST' !== $request->getMethod()) {
            $terminForm->setData($terminEntity->getArrayCopy());
            return new HtmlResponse($this->templateRenderer->render('app::termin/mng/update', $viewData));
        }

        $terminForm->setData(array_merge_recursive($request->getParsedBody(), $request->getUploadedFiles()));

        // process
        if (!$terminForm->isValid()) {
            return new HtmlResponse($this->templateRenderer->render('app::termin/mng/update', $viewData));
        }

        // save
        $terminEntity = $this->save($terminEntity, $terminForm);

        $this->flashMessages($request)->flash('secondary', 'default');

        return new RedirectResponse($this->getUrlpoolService()->get(query_params: ['date' => $terminEntity->getTerminDatumStart()], fragment: $terminEntity->getTerminDatumStart()));
    }
}
