<?php

declare(strict_types=1);

namespace App\Handler\Media\Mng;

use App\Handler\AbstractBaseHandler;
use App\Traits\Aware\FormStorageAwareTrait;
use App\Traits\Aware\MediaRepositoryAwareTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Form\FormInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_merge;

class MediaReadHandler extends AbstractBaseHandler
{
    use FormStorageAwareTrait;
    use MediaRepositoryAwareTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->getUrlpoolService()->save();

        // init
        $myInitConfig = $this->getMyInitConfig();
        $mediaRepository = $this->getMediaRepository();

        // datalist data
        $tagData = $mediaRepository->fetchTag();

        // form
        $mngMediaSearchForm = $this->getMediaSearchForm($request->getQueryParams());

        // view
        $viewData = [
            'myInitConfig'       => $myInitConfig,
            'mngMediaSearchForm' => $mngMediaSearchForm,
            'mediaArray'         => [],
            'datalist'           => array_merge($tagData),
        ];

        // check form
        if ($_GET === [] || !$mngMediaSearchForm->isValid()) {
            return new HtmlResponse($this->templateRenderer->render('app::media/mng/read', $viewData));
        }

        // ...
        $formData = $mngMediaSearchForm->getData();
        $searchValues = $this->getMappedMediaSearchValues($formData);
        $viewData['searchValues'] = $searchValues;

        // fetch media
        $mediaResultSet = $mediaRepository->fetchMedia($searchValues);

        // set view data
        $viewData['mediaArray'] = $mediaResultSet;

        return new HtmlResponse($this->templateRenderer->render('app::media/mng/read', $viewData));
    }

    public function getMediaSearchForm(array $params): FormInterface
    {
        $form = $this->getForm('media-mng-search-form');
        $form->setAttribute('method', 'GET');
        $form->setAttribute('action', '/manage/media-read');

        $form->setData($params);

        return $form;
    }

    public function getMappedMediaSearchValues(array $formData): array
    {
        return ['suchtext' => $formData['search_suchtext']];
    }
}
