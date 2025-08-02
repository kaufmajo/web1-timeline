<?php

declare(strict_types=1);

namespace App\Handler\Home\Mng;

use App\Handler\AbstractBaseHandler;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeReadHandler extends AbstractBaseHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->getUrlpoolService()->save();

        // view
        $viewData = [
            'myInitConfig' => $this->getMyInitConfig(),
        ];

        return new HtmlResponse(
            $this->templateRenderer->render('app::home/mng/read', $viewData)
        );
    }
}
