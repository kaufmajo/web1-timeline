<?php

declare(strict_types=1);

namespace App\Handler\Termin\Mng;

use App\Handler\Termin\AbstractTerminWriteHandler;
use App\Model\Termin\TerminEntity;
use App\Service\HelperService;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_merge_recursive;

class TerminInsertHandler extends AbstractTerminWriteHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // script will stop when ...
        HelperService::isPostMaxSizeReached();

        // init
        $dateParam    = (string)$request->getAttribute('p1');
        $terminEntity = new TerminEntity();

        // datalist data
        [$mitvonData, $kategorieData, $betreffData, $linkData, $linkTitelData, $imageData] = $this->datalistData();

        //form
        $terminForm = $this->getTerminForm();

        // view
        $viewData = [
            'myInitConfig' => $this->getMyInitConfig(),
            'terminForm'   => $terminForm,
            'terminEntity' => $terminEntity,
            'redirectUrl'  => $this->getUrlpoolService()->get(),
            'datalist'     => ['mitvon' => $mitvonData, 'kategorie' => $kategorieData, 'betreff' => $betreffData, 'link' => $linkData, 'link_titel' => $linkTitelData, 'image' => $imageData],
        ];

        if ('POST' !== $request->getMethod()) {

            $formData                       = $terminEntity->getArrayCopy();
            $formData['termin_datum_start'] = $dateParam;
            $formData['termin_datum_ende']  = $dateParam;

            $terminForm->setData($formData);

            return new HtmlResponse($this->templateRenderer->render('app::termin/mng/insert', $viewData));
        }

        $terminForm->setData(array_merge_recursive($request->getParsedBody(), $request->getUploadedFiles()));

        // process
        if (!$terminForm->isValid()) {
            return new HtmlResponse($this->templateRenderer->render('app::termin/mng/insert', $viewData));
        }

        // save
        $terminEntity = $this->save($terminEntity, $terminForm);

        $this->flashMessages($request)->flash('secondary', 'default');

        return new RedirectResponse($this->getUrlpoolService()->get(query_params: ['date' => $terminEntity->getTerminDatumStart()], fragment: $terminEntity->getTerminDatumStart()));
    }
}
