<?php

declare(strict_types=1);

namespace App\Model\Media;

use App\Model\AbstractRepository;
use App\Model\Entity\EntityHydratorInterface;
use App\Model\Entity\EntityInterface;
use App\Traits\Aware\DbalAwareTrait;
use Doctrine\DBAL\Connection;

class MediaRepository extends AbstractRepository implements MediaRepositoryInterface
{
    use DbalAwareTrait;

    public function __construct(Connection $dbalConnection, EntityHydratorInterface $entityHydrator, MediaEntity $prototype)
    {
        parent::__construct($dbalConnection, $entityHydrator, $prototype);
    }

    public function refreshEntity(MediaEntityInterface &$mediaEntity): void
    {
        $mediaEntity = $this->findEntityById($mediaEntity->getEntityId());
    }

    public function findEntityById(int $entityId): ?MediaEntityInterface
    {
        return $this->findMediaById($entityId);
    }

    public function findMediaById(int $id): null|MediaEntityInterface|EntityInterface
    {
        $qb = $this->dbalConnection->createQueryBuilder();

        $qb->select('*')
            ->from('tajo1_media', 't1')
            ->where('t1.media_id = ?')
            ->setParameter(0, $id);

        $result = $qb->fetchAssociative();

        if (!$result) {
            return null;
        }

        $entity = $this->hydrateEntity($result);

        return $this->mapReferences($entity);
    }

    public function mapReferences(?EntityInterface $entity): ?EntityInterface
    {
        return $entity;
    }

    public function fetchMedia(array $params = [], string $order = 'media_aktualisiert_am DESC, media_id DESC'): array
    {
        $qb = $this->dbalConnection->createQueryBuilder();
        $qb->select(
            't1.media_id',
            't1.media_parent_id',
            't1.media_name',
            't1.media_groesse',
            't1.media_mimetype',
            't1.media_von',
            't1.media_bis',
            't1.media_anzeige',
            't1.media_hash',
            't1.media_tag',
            't1.media_privat',
            't1.media_erstellt_am',
            't1.media_aktualisiert_am',
            't2.termin_id',
        )
            ->addSelect('IF(CURDATE() BETWEEN `t1`.`media_von` AND `t1`.`media_bis`, 1, 0) AS _gueltig')
            ->addSelect('(SELECT COUNT(`c1`.`media_id`) FROM `tajo1_media` AS `c1` WHERE `c1`.`media_parent_id` = `t1`.`media_id`) AS _hatVersion')
            ->from('tajo1_media', 't1')
            ->leftJoin(
                't1',
                'tajo1_termin',
                't2',
                'REGEXP_REPLACE(t2.termin_link,"/media/([0-9]+)","\\\\1") = t1.media_id OR REGEXP_REPLACE(t2.termin_image,"/media/([0-9]+)","\\\\1") = t1.media_id'
            )
            ->groupBy('t1.media_id')
            ->orderBy($order);

        // params
        if (isset($params['id']) && ! empty($params['id'])) {
            $qb->andWhere($qb->expr()->eq('t1.media_id', ':where_id'));
            $qb->setParameter('where_id', $params['id']);
        }

        // parent
        if (isset($params['parent']) && ! empty($params['parent'])) {
            $qb->andWhere($qb->expr()->eq('t1.media_parent_id', ':where_parent'));
            $qb->setParameter('where_parent', $params['parent']);
        } else {
            $qb->andWhere($qb->expr()->isNull('t1.media_parent_id'));
        }

        // von
        if (isset($params['von']) && ! empty($params['von'])) {
            $qb->andWhere(
                $qb->expr()->or(
                    $qb->expr()->gte(`t1` . `media_bis`, ':where_von'),
                    $qb->expr()->gte(`t1` . `media_von`, ':where_von'),
                )
            );
            $qb->setParameter('where_von', $params['von']);
        }

        // bis
        if (isset($params['bis']) && ! empty($params['bis'])) {
            $qb->andWhere(
                $qb->expr()->or(
                    $qb->expr()->lte(`t1` . `media_bis`, ':where_bis'),
                    $qb->expr()->lte(`t1` . `media_von`, ':where_bis'),
                )
            );
            $qb->setParameter('where_bis', $params['bis']);
        }

        // suchtext
        if (isset($params['suchtext']) && ! empty($params['suchtext'])) {

            $suchtext           = str_replace(['(', ')', '/', '\\'], ' ', $params['suchtext']);
            $suchtextParts      = array_filter(explode(',', trim($suchtext)), 'strlen');

            if (count($suchtextParts)) {

                foreach ($suchtextParts as $value) {

                    $whereLikeSearch = [];
                    $suchtextSubparts = array_filter(explode(' ', trim($value)), 'strlen');

                    foreach ($suchtextSubparts as $v) {

                        $v = trim($v);

                        if ('' !== $v) {

                            $suchtextFields = [];

                            foreach (
                                [
                                    ['column' => 't1.media_id', 'search' => '%[value]%'],
                                    ['column' => 't1.media_name', 'search' => '%[value]%'],
                                    ['column' => 't1.media_anzeige', 'search' => '%[value]%'],
                                    ['column' => 't1.media_tag', 'search' => '%[value]%'],
                                ] as $column
                            ) {
                                $quoteValue = $this->getDbalConnection()->quote(str_replace('[value]', $v, $column['search']));
                                $suchtextFields[] = $qb->expr()->like($column['column'], $quoteValue);
                            }
                        }

                        $whereLikeSearch[] = $qb->expr()->or(...$suchtextFields);
                    }

                    $qb->andWhere(...$whereLikeSearch);
                }
            }
        }

        // tag
        if (isset($params['tag']) && ! empty($params['tag'])) {
            $qb->andWhere($qb->expr()->eq('t1.media_tag', ':where_tag'));
            $qb->setParameter('where_tag', $params['tag']);
        }

        // privat
        if (isset($params['privat'])) {
            $qb->andWhere($qb->expr()->eq('t1.media_privat', ':where_privat'));
            $qb->setParameter('where_privat', $params['privat']);
        }

        // limit
        if (isset($params['limit']) && ! empty($params['limit'])) {
            $qb->setFirstResult(0)
                ->setMaxResults($params['limit']);
        }

        return $qb->fetchAllAssociative();
    }

    public function fetchTag(array $params = [], string $order = 'media_tag ASC'): array
    {
        $qb = $this->dbalConnection->createQueryBuilder();
        $qb->select('t1.media_tag')
            ->from('tajo1_media', 't1')
            ->andWhere(
                $qb->expr()->isNotNull('t4.termin_mitvon'),
                $qb->expr()->neq('t4.termin_mitvon', "''")
            )
            ->where($qb->expr()->isNull('t1.media_parent_id'))
            ->groupBy('t1.media_tag')
            ->orderBy($order);

        // params

        // label
        if (isset($params['tag']) && ! empty($params['tag'])) {
            $qb->andWhere($qb->expr()->eq('t1.media_tag', $params[':where_tag']));
            $qb->setParameter('where_tag', $params['tag']);
        }

        // return
        return $qb->fetchAllAssociative();
    }
}
