<?php

declare(strict_types=1);

namespace App\Handler\Home\Def;

use App\Traits\Aware\DbalAwareTrait;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TestHandler implements RequestHandlerInterface
{
    use DbalAwareTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // init
        $dbal = $this->getDbalConnection();

        $data = $dbal->fetchOne('SELECT * FROM tajo1_termin');

        return new JsonResponse(['result' => $data]);
    }
}
