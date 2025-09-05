<?php

declare(strict_types=1);

namespace App\Handler\Termin\Def;

use App\Handler\Termin\AbstractTerminHandler;
use App\Model\Termin\TerminCollection;
use DateTime;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Form\FormInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TerminSearchHandler extends AbstractTerminHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // init
        $terminRepository = $this->getTerminRepository();

        // collection
        $terminCollection = new TerminCollection();

        // datalist data
        $mitvonData    = $terminRepository->fetchMitvon($this->getMappedDatalistSearchValues());
        $kategorieData = $terminRepository->fetchKategorie($this->getMappedDatalistSearchValues());
        $betreffData   = $terminRepository->fetchBetreff($this->getMappedDatalistSearchValues());

        // form
        $defTerminSearchForm = $this->getTerminSearchForm();
        $defTerminSearchForm->setData($request->getQueryParams());
        $isFormValid  = $defTerminSearchForm->isValid();
        $formData     = $defTerminSearchForm->getData();
        $searchValues = $this->getMappedTerminSearchValues($formData);

        // view
        $viewData = [
            'terminCollection'      => $terminCollection,
            'defTerminSearchForm'   => $defTerminSearchForm,
            'datalist'              => array_merge([['Sonntag'], ['Montag'], ['Dienstag'], ['Mittwoch'], ['Donnerstag'], ['Freitag'], ['Samstag']], $kategorieData, $betreffData, $mitvonData),
            'redirectUrl'           => $this->getUrlpoolService()->get(),
        ];

        if ($_GET === [] || !$isFormValid) {

            return new HtmlResponse(
                $this->templateRenderer->render('app::termin/def/search', $viewData)
            );
        }

        // fetch termin
        $terminResultSet = $terminRepository->fetchTermin($searchValues, ['t4.termin_id']);

        // init collection
        $terminCollection->init($terminResultSet);

        // send response to client
        return new HtmlResponse($this->templateRenderer->render('app::termin/def/search', $viewData), 200);
    }

    public function getTerminSearchForm(): FormInterface
    {
        $form = $this->getForm('def-termin-search-form');
        $form->setAttribute('method', 'GET');
        $form->setAttribute('action', '/search');

        // set default data
        $form->setData([
            'search_suchtext' => '',
        ]);

        return $form;
    }

    public function getMappedTerminSearchValues(array $formData): array
    {
        return $this->getMappedDefSearchValues([
            'anzeige'   => true,
            'start'     => (new DateTime())->format('Y-m-d'),
            'suchtext'  => $formData['search_suchtext'] ?? '',
        ]);
    }

    public function getMappedDatalistSearchValues(): array
    {
        return $this->getMappedDefSearchValues([
            'anzeige'   => true,
            'start'     => (new DateTime())->format('Y-m-d'),
        ]);
    }
}
