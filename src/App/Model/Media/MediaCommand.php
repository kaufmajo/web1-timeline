<?php

declare(strict_types=1);

namespace App\Model\Media;

use App\Model\AbstractCommand;
use App\Model\Entity\EntityHydratorInterface;
use App\Provider\MediaStorageProvider;
use App\Traits\Aware\MediaRepositoryAwareTrait;
use Doctrine\DBAL\Connection;
use Laminas\Diactoros\UploadedFile;
use RuntimeException;

use function rename;

class MediaCommand extends AbstractCommand implements MediaCommandInterface
{
    use MediaRepositoryAwareTrait;

    public function __construct(Connection $dbalConnection, EntityHydratorInterface $entityHydrator)
    {
        parent::__construct($dbalConnection, $entityHydrator);
    }

    public function insertMedia(MediaEntityInterface $mediaEntity): int
    {
        return $this->insert('tajo1_media', $mediaEntity);
    }

    public function updateMedia(MediaEntityInterface $mediaEntity): int
    {
        return $this->update('tajo1_media', $mediaEntity, 'media_id');
    }

    public function deleteMedia(MediaEntityInterface $mediaEntity): int
    {
        return $this->delete('tajo1_media', $mediaEntity, 'media_id');
    }

    public function saveMedia(MediaEntityInterface $mediaEntity): int
    {
        if (! $mediaEntity->getMediaId()) {
            return $this->insertMedia($mediaEntity);
        } else {
            return $this->updateMedia($mediaEntity);
        }
    }

    public function storeMedia(MediaEntityInterface $mediaEntity, UploadedFile $uploadedFile): void
    {
        // user selects a file
        if (0 === $uploadedFile->getError()) {
            // create a version of the current media
            $this->storeMediaVersion($mediaEntity);

            // set file properties for new media
            $mediaEntity->setMediaName($uploadedFile->getClientFilename());
            $mediaEntity->setMediaGroesse($uploadedFile->getSize());
            $mediaEntity->setMediaMimetype($uploadedFile->getClientMediaType()); // 'application/octet-stream'

            // save new media entity
            $this->saveMedia($mediaEntity);
            // refresh mediaEntity to get new created hash value from DB
            $this->getMediaRepository()->refreshEntity($mediaEntity);

            // finally ... move new file to storage
            $uploadedFile->moveTo(
                MediaStorageProvider::createDirPath($mediaEntity)::getFilePath($mediaEntity)
            );
        } else {
            // save new media entity
            $this->saveMedia($mediaEntity);
        }
    }

    public function storeMediaVersion(MediaEntityInterface $mediaEntity): void
    {
        $mediaVersionEntity = $this->getMediaVersionEntity($mediaEntity);

        // save version => but only if it's an update = media is already in storage
        if (MediaStorageProvider::isInStorage($mediaEntity)) {
            // save previous media entity
            $this->saveMedia($mediaVersionEntity);
            // refresh mediaEntity to get new created hash value from DB
            $this->getMediaRepository()->refreshEntity($mediaVersionEntity);

            rename(
                MediaStorageProvider::getFilePath($mediaEntity),
                MediaStorageProvider::createDirPath($mediaVersionEntity)::getFilePath($mediaVersionEntity)
            );
        }
    }

    public function getMediaVersionEntity(MediaEntityInterface $mediaEntity): MediaEntityInterface
    {
        // reset properties
        $mediaVersionEntity = new MediaEntity();
        $mediaVersionEntity->setMediaId(null);
        $mediaVersionEntity->setMediaPrivat(1);
        $mediaVersionEntity->setMediaVon('1999-01-01');
        $mediaVersionEntity->setMediaBis('2100-01-01');
        $mediaVersionEntity->setMediaTag(null);
        // assign values from current mediaEntity
        $mediaVersionEntity->setMediaParentId($mediaEntity->getMediaId());
        $mediaVersionEntity->setMediaName($mediaEntity->getMediaName());
        $mediaVersionEntity->setMediaAnzeige($mediaEntity->getMediaAnzeige());
        $mediaVersionEntity->setMediaGroesse($mediaEntity->getMediaGroesse());
        $mediaVersionEntity->setMediaMimetype($mediaEntity->getMediaMimetype());

        return $mediaVersionEntity;
    }
}
