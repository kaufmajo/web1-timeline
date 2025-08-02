<?php

declare(strict_types=1);

namespace App\Handler\Home\Mng;

use App\Handler\AbstractBaseHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function phpinfo;

use const INFO_GENERAL;

class HomePhpinfoHandler extends AbstractBaseHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        phpinfo(INFO_ALL);
        //phpinfo(INFO_GENERAL);

        exit;
    }
}
