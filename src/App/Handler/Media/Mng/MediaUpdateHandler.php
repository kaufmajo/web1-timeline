<?php

declare(strict_types=1);

namespace App\Handler\Media\Mng;

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

class MediaUpdateHandler extends AbstractBaseHandler
{
    use FormStorageAwareTrait;

    use MediaCommandAwareTrait;

    use MediaEntityTrait;
    
    use MediaRepositoryAwareTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // script will stop when ...
        HelperService::isPostMaxSizeReached();

        // param
        $mediaIdParam = (int) $request->getAttribute('p1');

        // init
        $myInitConfig    = $this->getMyInitConfig();
        $mediaCommand    = $this->getMediaCommand();
        $mediaRepository = $this->getMediaRepository();

        // datalist data
        $tagData = $mediaRepository->fetchTag();

        // mediaEntity
        $mediaEntity = $this->getMediaEntityById($mediaIdParam);

        //form
        $mediaForm = $this->getMediaForm();
        $mediaForm->setData($mediaEntity->getArrayCopy());
        $mediaForm->getInputFilter()->get('media_datei')->setRequired(false);

        // view
        $viewData = [
            'myInitConfig' => $myInitConfig,
            'mediaForm'    => $mediaForm,
            'mediaEntity'  => $mediaEntity,
            'redirectUrl'  => $this->getUrlpoolService()->get(fragment: $mediaEntity->getMediaId()),
            'datalist'     => ['tag' => $tagData],
        ];

        if ('POST' !== $request->getMethod()) {
            return new HtmlResponse(
                $this
                    ->templateRenderer
                    ->render('app::media/mng/update', $viewData)
            );
        }

        $mediaForm->setData(array_merge_recursive(
            $request->getParsedBody(),
            $request->getUploadedFiles()
        ));

        // process
        if (!$mediaForm->isValid()) {
            return new HtmlResponse(
                $this
                    ->templateRenderer
                    ->render('app::media/mng/update', $viewData)
            );
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
