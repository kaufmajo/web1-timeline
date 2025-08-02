<?php

declare(strict_types=1);

namespace App\Handler\Termin\Mng;

use App\Handler\Termin\AbstractTerminHandler;
use App\Model\Termin\TerminCollection;
use App\Service\HelperService;
use App\Traits\Aware\FormStorageAwareTrait;
use App\Traits\Aware\TerminRepositoryAwareTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Form\FormInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TerminSearchHandler extends AbstractTerminHandler
{
    use FormStorageAwareTrait;
    use TerminRepositoryAwareTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->getUrlpoolService()->save();

        // init
        $terminRepository = $this->getTerminRepository();

        // collection
        $terminCollection = new TerminCollection();

        // datalist data
        $mitvonData    = $terminRepository->fetchMitvon($this->getMappedDatalistSearchValues());
        $kategorieData = $terminRepository->fetchKategorie($this->getMappedDatalistSearchValues());
        $betreffData   = $terminRepository->fetchBetreff($this->getMappedDatalistSearchValues());

        // form
        $defTerminSearchForm = $this->getTerminSearchForm([]);
        $defTerminSearchForm->setData($request->getQueryParams());
        $isFormValid  = $defTerminSearchForm->isValid();
        $formData     = $defTerminSearchForm->getData();
        $searchValues = $this->getMappedTerminSearchValues($formData);

        // view
        $viewData = [
            'terminCollection'      => $terminCollection,
            'defTerminSearchForm'   => $defTerminSearchForm,
            'datalist'              => array_merge([['Sonntag'], ['Montag'], ['Dienstag'], ['Mittwoch'], ['Donnerstag'], ['Freitag'], ['Samstag']], $kategorieData, $betreffData, $mitvonData),
        ];

        if (empty($_GET) || !$isFormValid) {

            return new HtmlResponse(
                $this->templateRenderer->render('app::termin/mng/search', $viewData)
            );
        }

        // fetch termin
        $terminResultSet = $terminRepository->fetchTermin($searchValues, ['t4.termin_id']);

        // init collection
        $terminCollection->init($terminResultSet);

        return new HtmlResponse(
            $this->templateRenderer->render('app::termin/mng/search', $viewData)
        );
    }

    public function getTerminSearchForm(array $params): FormInterface
    {
        $form = $this->getForm('def-termin-search-form');
        $form->setAttribute('method', 'GET');
        $form->setAttribute('action', '/manage/termin-search#' . HelperService::getAnchorString(date('Y-m-d')));

        // set default data
        $form->setData($params);

        return $form;
    }

    public function getMappedTerminSearchValues(array $formData): array
    {
        return $this->getMappedDefSearchValues([
            'anzeige' => true,
            'suchtext' => $formData['search_suchtext'] ?? '',
        ]);
    }

    public function getMappedDatalistSearchValues(): array
    {
        return $this->getMappedDefSearchValues([
            'anzeige' => true,
        ]);
    }
}
