<?php

declare(strict_types=1);

namespace App\Handler\Media\Mng;

use App\Handler\AbstractBaseHandler;
use App\Traits\Aware\MediaRepositoryAwareTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MediaVersionHandler extends AbstractBaseHandler
{
    use MediaRepositoryAwareTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // param
        $mediaIdParam = (int) $request->getAttribute('p1');

        // init
        $myInitConfig    = $this->getMyInitConfig();
        $mediaRepository = $this->getMediaRepository();

        // view
        $viewData = [
            'myInitConfig' => $myInitConfig,
            'redirectUrl'  => $this->getUrlpoolService()->get(fragment: $mediaIdParam),
        ];

        // fetch media
        $mediaResultSet = $mediaRepository->fetchMedia(['parent' => $mediaIdParam]);

        // set view data
        $viewData['mediaArray'] = $mediaResultSet;

        return new HtmlResponse(
            $this->templateRenderer->render('app::media/mng/version', $viewData)
        );
    }
}
