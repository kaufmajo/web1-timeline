<?php

declare(strict_types=1);

namespace App\Handler\Media\Mng;

use App\Handler\AbstractBaseHandler;
use App\Traits\Aware\FormStorageAwareTrait;
use App\Traits\Aware\MediaCommandAwareTrait;
use App\Traits\Aware\MediaRepositoryAwareTrait;
use App\Traits\Entity\MediaEntityTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MediaDeleteHandler extends AbstractBaseHandler
{
    use FormStorageAwareTrait;

    use MediaCommandAwareTrait;

    use MediaEntityTrait;

    use MediaRepositoryAwareTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // param
        $mediaIdParam = (int) $request->getAttribute('p1');

        // init
        $myInitConfig = $this->getMyInitConfig();
        $mediaCommand = $this->getMediaCommand();
        $mediaEntity  = $this->getMediaEntityById($mediaIdParam);

        // view
        $viewData = [
            'myInitConfig' => $myInitConfig,
            'mediaEntity'  => $mediaEntity,
            'redirectUrl'  => $this->getUrlpoolService()->get(fragment: $mediaEntity->getMediaId()),
        ];

        // ask for confirmation
        if ('POST' !== $request->getMethod()) {
            return new HtmlResponse($this->templateRenderer->render('app::media/mng/delete', $viewData));
        }

        // redirect if confirmation is not given
        if (
            $mediaIdParam !== (int) $request->getParsedBody()['id'] ||
            !isset($request->getParsedBody()['confirm']) ||
            'Löschen' !== $request->getParsedBody()['confirm']
        ) {
            return new RedirectResponse($this->getUrlpoolService()->get(fragment: $mediaEntity->getMediaId()));
        }

        // ok ... now execute delete
        $mediaCommand->deleteMedia($mediaEntity);

        $this->flashMessages($request)->flash('info', 'default');

        return new RedirectResponse($this->getUrlpoolService()->get(fragment: $mediaEntity->getMediaId()));
    }
}
