<?php

declare(strict_types=1);

namespace App\Handler\Termin\Def;

use App\Form\Search\DefTerminSearchInputFilter;
use App\Handler\Termin\AbstractTerminHandler;
use App\Model\Termin\TerminCollection;
use DateTime;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TerminIcsHandler extends AbstractTerminHandler
{
    public function __construct(protected DefTerminSearchInputFilter $defTerminSearchInputFilter)
    {
        $this->defTerminSearchInputFilter->init();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // param
        $terminIdParam = (int) $request->getAttribute('p1');

        // init
        $terminRepository = $this->getTerminRepository();

        // collection
        $terminCollection = new TerminCollection();

        // inputFilter
        $this->defTerminSearchInputFilter->setData($request->getQueryParams());
        $isInputValid = $this->defTerminSearchInputFilter->isValid();
        $inputData    = $this->defTerminSearchInputFilter->getValues();

        // view
        $viewData = [
            'terminCollection' => $terminCollection,
        ];

        if (! $isInputValid) {
            return new HtmlResponse(
                $this->templateRenderer->render('app::termin/def/ics', $viewData)
            );
        }

        //search values
        $searchValues            = $this->getMappedTerminSearchValues($inputData);
        $searchValues['id']      = $terminIdParam;
        $searchValues['drucken'] = 1;

        // fetch termin
        $terminResultSet = $terminRepository->fetchTermin($searchValues, ['t4.termin_id']);

        // init collection
        $terminCollection->init($terminResultSet);

        // Set HTTP headers
        $headers['Content-Type']        = 'text/calendar; charset=utf-8';
        $headers['Content-Disposition'] = 'attachment; filename="cal.ics"';
        $headers['Pragma']              = 'public';
        $headers['Expires']             = '0';
        $headers['Cache-Control']       = 'must-revalidate';

        // send response to client
        return new TextResponse($this->templateRenderer->render('app::termin/def/ics', $viewData), 200, $headers);
    }

    public function getMappedTerminSearchValues(array $formData): array
    {
        return $this->getMappedDefSearchValues([
            'anzeige'   => true,
            'start'     => (new DateTime())->format('Y-m-d'),
            'suchtext'  => $formData['search_suchtext'] ?? '',
        ]);
    }
}
