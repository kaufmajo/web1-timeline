<?php

declare(strict_types=1);

namespace App\Handler\Auth\Def;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LogoutHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Session $session */
        $session = $request->getAttribute('session');

        $session->clear();

        return new RedirectResponse('/');
    }
}
