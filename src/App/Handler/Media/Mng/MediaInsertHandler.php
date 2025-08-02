<?php

declare(strict_types=1);

namespace App\Handler\Media\Mng;

use App\Enum;
use App\Model\Media\MediaEntity;
use App\Handler\AbstractBaseHandler;
use App\Service\HelperService;
use App\Traits\Aware\FormStorageAwareTrait;
use App\Traits\Aware\MediaCommandAwareTrait;
use App\Traits\Aware\MediaRepositoryAwareTrait;
use App\Traits\Entity\MediaEntityTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Form\Form;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_merge_recursive;

class MediaInsertHandler extends AbstractBaseHandler
{
    use FormStorageAwareTrait;

    use MediaCommandAwareTrait;

    use MediaEntityTrait;

    use MediaRepositoryAwareTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // script will stop when ...
        HelperService::isPostMaxSizeReached();

        // init
        $myInitConfig    = $this->getMyInitConfig();
        $mediaCommand    = $this->getMediaCommand();
        $mediaRepository = $this->getMediaRepository();
        $mediaEntity = new MediaEntity();

        // datalist data
        $tagData = $mediaRepository->fetchTag();

        //form
        $mediaForm = $this->getMediaForm();

        // view
        $viewData = [
            'myInitConfig' => $myInitConfig,
            'mediaForm'    => $mediaForm,
            'mediaEntity'  => $mediaEntity,
            'redirectUrl'  => $this->getUrlpoolService()->get(),
            'datalist'     => ['tag' => $tagData],
        ];

        if ('POST' !== $request->getMethod()) {
            return new HtmlResponse($this->templateRenderer->render('app::media/mng/insert', $viewData));
        }

        $mediaForm->setData(array_merge_recursive($request->getParsedBody(), $request->getUploadedFiles()));

        // process
        if (!$mediaForm->isValid()) {
            return new HtmlResponse($this->templateRenderer->render('app::media/mng/insert', $viewData));
        }

        $formData = $mediaForm->getData();

        $mediaEntity->exchangeArray($formData);

        $mediaCommand->storeMedia($mediaEntity, $formData['media_datei']);

        $this->flashMessages($request)->flash('info', 'default');

        return new RedirectResponse($this->getUrlpoolService()->get(fragment: $mediaEntity->getMediaId()));
    }

    public function getMediaForm(): Form
    {
        /** @var Form $mediaForm */
        $mediaForm = $this->getForm('media-form');
        $mediaForm->setAttribute('method', 'POST');
        $mediaForm->setAttribute('enctype', 'multipart/form-data');

        return $mediaForm;
    }
}
