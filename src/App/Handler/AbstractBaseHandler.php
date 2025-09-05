<?php

declare(strict_types=1);

namespace App\Handler;

use App\Traits\Aware\ConfigAwareTrait;
use App\Traits\Aware\LoggerAwareTrait;
use App\Traits\Aware\TemplateRendererAwareTrait;
use App\Traits\Aware\UrlHelperAwareTrait;
use App\Traits\Aware\UrlpoolServiceAwareTrait;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AbstractBaseHandler implements RequestHandlerInterface
{
    use ConfigAwareTrait;

    use LoggerAwareTrait;

    use TemplateRendererAwareTrait;

    use UrlHelperAwareTrait;

    use UrlpoolServiceAwareTrait;

    protected ?FlashMessagesInterface $flashMessages = null;

    public function flashMessages(ServerRequestInterface $request): FlashMessagesInterface
    {
        if (!$this->flashMessages instanceof FlashMessagesInterface) {

            $this->flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        }

        return $this->flashMessages;
    }
}
