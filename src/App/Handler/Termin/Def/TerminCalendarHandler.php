<?php

declare(strict_types=1);

namespace App\Handler\Termin\Def;

use App\Handler\Termin\AbstractTerminHandler;
use App\Model\Termin\TerminCollection;
use App\Service\HelperService;
use DateTime;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Validator\Date;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TerminCalendarHandler extends AbstractTerminHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->getUrlpoolService()->save();

        // param
        $dateParam = (string)($request->getQueryParams()['date'] ?? (new DateTime())->format('Y-m-d'));
        $terminIdParam = (int)($request->getQueryParams()['id'] ?? 0);

        if (!(new Date())->isValid($dateParam)) {
            return new TextResponse('No valid date is given');
        }

        // init
        $terminRepository = $this->getTerminRepository();

        // collection
        $terminCollection = new TerminCollection(referenzDatum: $dateParam);

        // view
        $viewData = [
            'terminCollection' => $terminCollection,
            'terminIdParam' => $terminIdParam,
            'dateParam' => $dateParam,
        ];

        // fetch termin
        $terminResultSet = $terminRepository->fetchTermin($this->getMappedCalendarSearchValues($dateParam));

        // init collection
        $terminCollection->init($terminResultSet);

        // send response to client
        return new HtmlResponse($this->templateRenderer->render('app::termin/def/calendar', $viewData), 200);
    }

    public function getMappedCalendarSearchValues(string $date): array
    {
        return $this->getMappedDefSearchValues([
            'start'     => HelperService::getMonthFirstDayForCalender($date)->format('Y-m-d'),
            'ende'      => HelperService::getMonthLastDayForCalender($date)->format('Y-m-d'),
            'anzeige'   => false,
        ]);
    }
}
