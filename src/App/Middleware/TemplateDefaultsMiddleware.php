<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Service\UrlpoolService;
use Mezzio\Authentication\UserInterface;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Router\RouteResult;
use Mezzio\Session\RetrieveSession;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TemplateDefaultsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly TemplateRendererInterface $templateRenderer
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = RetrieveSession::fromRequest($request);

        // Inject the current user, or null if there isn't one.
        $this->templateRenderer->addDefaultParam(
            TemplateRendererInterface::TEMPLATE_ALL,
            'security', // This is named security so it will not interfere with your user admin pages
            $request->getAttribute(UserInterface::class)
        );

        // Inject the currently matched route name.
        $routeResult = $request->getAttribute(RouteResult::class);
        $this->templateRenderer->addDefaultParam(
            TemplateRendererInterface::TEMPLATE_ALL,
            'matchedRouteName',
            $routeResult ? $routeResult->getMatchedRouteName() : null
        );

        // Inject all flash messages
        /** @var FlashMessagesInterface $flashMessages */
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        $this->templateRenderer->addDefaultParam(
            TemplateRendererInterface::TEMPLATE_ALL,
            'notifications',
            $flashMessages ? $flashMessages->getFlashes() : []
        );

        // Inject Urlpool service
        $urlpoolService = $request->getAttribute(UrlpoolService::class);
        $this->templateRenderer->addDefaultParam(
            TemplateRendererInterface::TEMPLATE_ALL,
            'urlpool',
            $routeResult ? $urlpoolService : null
        );

        // Inject Color
        $color = (int)($request->getQueryParams()['color'] ?? $session->get('color') ?? random_int(0, 7));
        $session->set('color', $color >= 0 && $color <= 7 ? $color : 0);
        $this->templateRenderer->addDefaultParam(
            TemplateRendererInterface::TEMPLATE_ALL,
            'color',
            $session->get('color')
        );

        // Inject any other data you always need in all your templates...

        return $handler->handle($request);
    }
}
