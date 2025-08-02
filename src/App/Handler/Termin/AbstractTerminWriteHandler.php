<?php

declare(strict_types=1);

namespace App\Handler\Termin;

use App\Model\Media\MediaEntity;
use App\Model\Termin\TerminEntityInterface;
use App\Service\HelperService;
use App\Traits\Aware\FormStorageAwareTrait;
use App\Traits\Aware\MediaCommandAwareTrait;
use App\Traits\Aware\TerminCommandAwareTrait;
use DateInterval;
use DateTime;
use Laminas\Diactoros\UploadedFile;
use Laminas\Form\Form;

abstract class AbstractTerminWriteHandler extends AbstractTerminHandler
{
    use FormStorageAwareTrait;

    use MediaCommandAwareTrait;
    
    use TerminCommandAwareTrait;

    public function save(TerminEntityInterface $terminEntity, Form $terminForm): TerminEntityInterface
    {
        // init
        $mediaEntity   = null;
        $mediaUrl     = [];
        $mediaCommand  = $this->getMediaCommand();
        $terminCommand = $this->getTerminCommand();

        // form data
        $formData = $terminForm->getData();
        $terminEntity->exchangeArray($formData);
        $mediaDatumEnde = ($terminEntity->isSerie()
            ? DateTime::createFromInterface(HelperService::getSeriePeriod(
                $terminEntity->getTerminDatumStart(),
                $terminEntity->getTerminSerieEnde(),
                $terminEntity->getTerminSerieWiederholung()
            )->getEndDate())
            : new DateTime($terminEntity->getTerminDatumEnde()))->add(new DateInterval('P31D'))->format('Y-m-d');

        // store media
        if (!$mediaEntity) {
            foreach (['media_datei_link', 'media_datei_link2', 'media_datei_bild'] as $media) {
                // user selects a file
                if ($formData[$media] instanceof UploadedFile && 0 === $formData[$media]->getError()) {
                    $mediaEntity = new MediaEntity();
                    $mediaEntity->setMediaTag('Terminformular');
                    $mediaEntity->setMediaVon(date('Y-m-d'));
                    $mediaEntity->setMediaBis($mediaDatumEnde);
                    $mediaEntity->setMediaPrivat(0);
                    $mediaCommand->storeMedia($mediaEntity, $formData[$media]);
                    $mediaUrl[$media] = '/media/' . $mediaEntity->getMediaId();
                }
            }
        } else {
            $mediaEntity->setMediaBis($mediaDatumEnde);
            $mediaCommand->saveMedia($mediaEntity);
        }

        // save termin
        $terminEntity->setTerminLink($mediaUrl['media_datei_link'] ?? $terminEntity->getTerminLink());
        $terminEntity->setTerminLink2($mediaUrl['media_datei_link2'] ?? $terminEntity->getTerminLink2());
        $terminEntity->setTerminImage($mediaUrl['media_datei_bild'] ?? $terminEntity->getTerminImage());
        $terminCommand->saveTermin($terminEntity);

        return $terminEntity;
    }

    public function getTerminForm(): Form
    {
        /** @var Form $terminForm */
        $terminForm = $this->getForm('termin-form');
        $terminForm->setAttribute('method', 'POST');
        $terminForm->setAttribute('enctype', 'multipart/form-data');
        //$terminForm->bind($terminEntity); --> not used, because of multipart/form-data file-upload
        //$terminForm->setData($formData);

        /**
         * ansicht Element - set value
         *
         * @var TerminAnsichtElementSelect $ansichtElement
         */
        $ansichtElement = $terminForm->get('termin_ansicht');
        $ansichtElement->setValueOptions($ansichtElement->getValueOptionsFromConfig());

        /**
         * status Element - set value
         *
         * @var TerminStatusElementSelect $statusElement
         */
        $statusElement = $terminForm->get('termin_status');
        $statusElement->setValueOptions($statusElement->getValueOptionsFromConfig());


        // $terminForm->get('termin_datum_start')->setAttributes(['disabled' => 'disabled']);
        // $terminForm->get('termin_datum_ende')->setAttributes(['disabled' => 'disabled']);
        // $terminForm->get('termin_serie_intervall')->setAttributes(['disabled' => 'disabled']);
        // $terminForm->get('termin_serie_wiederholung')->setAttributes(['disabled' => 'disabled']);
        // $terminForm->get('termin_serie_ende')->setAttributes(['disabled' => 'disabled']);

        // $terminForm->setValidationGroup([
        //     'termin_id',
        //     'termin_ansicht',
        //     'termin_status',
        //     'termin_zeit_start',
        //     'termin_zeit_ende',
        //     'termin_zeit_ganztags',
        //     'termin_betreff',
        //     'termin_text',
        //     'termin_kategorie',
        //     'termin_mitvon',
        //     'termin_image',
        //     'termin_link',
        //     'termin_link_titel',
        //     'termin_link2',
        //     'termin_link2_titel',
        //     'termin_zeige_konflikt',
        //     'termin_aktiviere_drucken',
        //     'termin_ist_konfliktrelevant',
        //     'termin_zeige_einmalig',
        //     'termin_zeige_tagezuvor',
        //     'termin_notiz',
        //     'media_datei_link',
        //     'media_datei_bild',
        // ]);


        return $terminForm;
    }

    public function datalistData(): array
    {
        // init
        $terminRepository = $this->getTerminRepository();

        $mitvonData    = $terminRepository->fetchMitvon();
        $kategorieData = $terminRepository->fetchKategorie();
        $betreffData   = $terminRepository->fetchBetreff();
        $linkData      = $terminRepository->fetchLink();
        $linkTitelData = $terminRepository->fetchLinkTitel();
        $imageData     = $terminRepository->fetchImage();

        return [$mitvonData, $kategorieData, $betreffData, $linkData, $linkTitelData, $imageData];
    }
}
