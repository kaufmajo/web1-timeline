<?php

declare(strict_types=1);

namespace App\Model\Termin;

use App\Enum\TerminStatusEnum;
use App\Model\AbstractRepository;
use App\Model\Entity\EntityHydratorInterface;
use App\Model\Entity\EntityInterface;
use App\Traits\Aware\ConfigAwareTrait;
use App\Traits\Aware\DbalAwareTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class TerminRepository extends AbstractRepository implements TerminRepositoryInterface
{
    use ConfigAwareTrait;

    use DbalAwareTrait;

    public function __construct(Connection $dbalConnection, EntityHydratorInterface $entityHydrator, TerminEntity $prototype)
    {
        parent::__construct($dbalConnection, $entityHydrator, $prototype);
    }

    public function findEntityById(int $entityId): TerminEntityInterface
    {
        return $this->findTerminById($entityId);
    }

    public function findTerminById(int $id): null|TerminEntityInterface|EntityInterface
    {
        $qb = $this->dbalConnection->createQueryBuilder();

        $qb->select('*')
            ->from('tajo1_termin', 't4')
            ->where('t4.termin_id = ?')
            ->setParameter(0, $id);

        $result = $qb->fetchAssociative();

        if (!$result) return null;

        $entity = $this->hydrateEntity($result);

        return $this->mapReferences($entity);
    }

    public function mapReferences(?EntityInterface $entity): ?EntityInterface
    {
        return $entity;
    }

    protected function getTerminJoinCondition(QueryBuilder $qb, array $params = []): string
    {
        $conditions = [];
        $expr = $qb->expr();

        $conditions[] = $expr->and(
            $expr->eq('t4.datum_id', 't3.datum_id'),
            $expr->eq('t4.termin_ist_geloescht', ':join_ist_geloescht')
        );
        $qb->setParameter('join_ist_geloescht', 0, \Doctrine\DBAL\ParameterType::INTEGER);

        // id
        if (isset($params['id']) && !empty($params['id'])) {
            $conditions[] = $expr->eq('t4.termin_id', 'join_id');
            $qb->setParameter('join_id', $params['id']);
        }

        // suchtext
        if (!empty($params['suchtext'])) {

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
                                    ['column' => 't4.termin_mitvon', 'search' => '%[value]%'],
                                    ['column' => 't4.termin_id', 'search' => '%[value]%'],
                                    ['column' => 't4.termin_kategorie', 'search' => '%[value]%'],
                                    ['column' => 't4.termin_betreff', 'search' => '%[value]%'],
                                    ['column' => 't4.termin_text', 'search' => '%[value]%'],
                                    ['column' => 't3.datum_datum', 'search' => '[value]%'],
                                    ['column' => 't3.datum_wochentag_lang_de', 'search' => '[value]%'],
                                    ['column' => 't3.datum_wochentag_lang_en', 'search' => '[value]%'],
                                    ['column' => 't3.datum_monat_lang_de', 'search' => '[value]%'],
                                    ['column' => 't3.datum_monat_lang_en', 'search' => '[value]%'],
                                    ['column' => 't3.datum_monat', 'search' => '[value]'],
                                    ['column' => 't3.datum_datum_1_de', 'search' => '[value]%'],
                                    ['column' => 't3.datum_datum_2_de', 'search' => '[value]%'],
                                    ['column' => 't3.datum_datum_3_de', 'search' => '[value]%'],
                                    ['column' => 't3.datum_datum_4_de', 'search' => '[value]%'],
                                    ['column' => 't3.datum_datum_5_de', 'search' => '[value]%'],
                                    ['column' => 't3.datum_datum_6_de', 'search' => '[value]%'],
                                    ['column' => 't3.datum_datum_7_de', 'search' => '[value]%'],
                                    ['column' => 't3.datum_datum_8_de', 'search' => '[value]%'],
                                ] as $column
                            ) {
                                $quoteValue = $this->getDbalConnection()->quote(str_replace('[value]', $v, $column['search']));
                                $suchtextFields[] = $expr->like($column['column'], $quoteValue);
                            }
                        }

                        $whereLikeSearch[] = $expr->or(...$suchtextFields);
                    }

                    $conditions[] = $expr->and(...$whereLikeSearch);
                }
            }
        }

        // kategorie
        if (!empty($params['kategorie'])) {
            $conditions[] = $expr->in('t4.termin_kategorie', ':join_kategorie');
            $qb->setParameter('join_kategorie', $params['kategorie'], \Doctrine\DBAL\ArrayParameterType::STRING);
        }

        // status
        if (!empty($params['status'])) {
            $conditions[] = $expr->in('t4.termin_status', ':join_status');
            $qb->setParameter('join_status', $params['status'], \Doctrine\DBAL\ArrayParameterType::STRING);
        }

        // ansicht
        if (!empty($params['ansicht'])) {
            $conditions[] = $expr->in('t4.termin_ansicht', ':join_ansicht');
            $qb->setParameter('join_ansicht', $params['ansicht'], \Doctrine\DBAL\ArrayParameterType::STRING);
        }

        // tagezuvor
        if (!empty($params['tagezuvor'])) {
            $conditions[] =
                $expr->or(
                    $expr->isNull('t4.termin_zeige_tagezuvor'),
                    $expr->lt('DATEDIFF(`t3`.`datum_datum`, CURRENT_DATE())', '`t4`.`termin_zeige_tagezuvor`')
                );
        }

        // drucken
        if (!empty($params['drucken'])) {
            $conditions[] = $expr->or(
                $expr->isNull('t4.termin_aktiviere_drucken'),
                $expr->eq('t4.termin_aktiviere_drucken', ':join_drucken')
            );
            $qb->setParameter('join_drucken', $params['drucken']);
        }

        return (string)$expr->and(...$conditions);
    }

    protected function getWhereCondition(QueryBuilder $qb, array $params = []): ?string
    {
        $conditions = [];
        $expr = $qb->expr();

        // start
        if (isset($params['start']) && !empty($params['start'])) {
            $conditions[] = $expr->gte('`t3`.`datum_datum`', ':where_start');
            $qb->setParameter('where_start', $params['start']);
        }

        // ende
        if (isset($params['ende']) && !empty($params['ende'])) {
            $conditions[] = $expr->lte('`t3`.`datum_datum`', ':where_ende');
            $qb->setParameter('where_ende', $params['ende']);
        }

        // anzeige
        if (isset($params['anzeige']) && $params['anzeige']) {
            $conditions[] = $expr->isNotNull('t4.termin_id');
        }

        // tage
        if (isset($params['tage']) && !empty($params['tage'])) {
            $conditions[] = $expr->in('t3.datum_wochentag', ':where_tage');
            $qb->setParameter('where_tage', $params['tage'], \Doctrine\DBAL\ArrayParameterType::INTEGER);
        }

        return $conditions ? (string)$expr->and(...$conditions) : '';
    }

    public function fetchTermin(
        array $params = [],
        array $groupBy = ['t3.datum_id', 't4.termin_id'],
        string $order = 't3.datum_datum ASC, t4.termin_zeit_start ASC, t4.termin_zeit_ende ASC, t4.termin_ansicht DESC, t4.termin_sortierung ASC'
    ): array {

        $initConfig = $this->getMyInitConfig();

        $cte1 = $this->dbalConnection->createQueryBuilder();
        $cte2 = $this->dbalConnection->createQueryBuilder();
        $cte3 = $this->dbalConnection->createQueryBuilder();

        $cte1
            ->select(
                't1.*',
                '`t2`.`datum_id`'
            )
            ->from('tajo1_lnk_datum_termin', 't2')
            ->leftJoin(
                't2',
                'tajo1_termin',
                't1',
                '`t1`.`termin_id` = `t2`.`termin_id`'
            );

        $cte2->select(
            'c1.termin_id',
            "GROUP_CONCAT(DISTINCT `c1`.`termin_status`, '---', `c1`.`termin_betreff`, '---', `c1`.`termin_mitvon`, '---', `c1`.`termin_id`, '---', `c1`.`termin_datum_start`, '---', `c1`.`termin_datum_ende` ORDER BY `c1`.`termin_betreff` SEPARATOR '+++') AS _konflikt"
        )
            ->from('tajo1_termin', 'c1')
            ->leftJoin(
                'c1',
                'cte1',
                't4',
                '
                `c1`.`termin_ist_konfliktrelevant` = 1 AND
                `c1`.`termin_ist_geloescht` = 0 AND
                `c1`.`termin_id` <> `t4`.`termin_id` AND 
                (
                        (TIMESTAMP(`t4`.`termin_datum_start`, `t4`.`termin_zeit_start`) BETWEEN TIMESTAMP(`c1`.`termin_datum_start`,`c1`.`termin_zeit_start`) AND TIMESTAMP(`c1`.`termin_datum_ende`,`c1`.`termin_zeit_ende`)) 
                        OR
                        (TIMESTAMP(`t4`.`termin_datum_ende`, `t4`.`termin_zeit_ende`) BETWEEN TIMESTAMP(`c1`.`termin_datum_start`,`c1`.`termin_zeit_start`) AND TIMESTAMP(`c1`.`termin_datum_ende`,`c1`.`termin_zeit_ende`))
                        OR
                        (TIMESTAMP(`c1`.`termin_datum_start`, `c1`.`termin_zeit_start`) BETWEEN TIMESTAMP(`t4`.`termin_datum_start`,`t4`.`termin_zeit_start`) AND TIMESTAMP(`t4`.`termin_datum_ende`,`t4`.`termin_zeit_ende`)) 
                        OR
                        (TIMESTAMP(`c1`.`termin_datum_ende`, `c1`.`termin_zeit_ende`) BETWEEN TIMESTAMP(`t4`.`termin_datum_start`,`t4`.`termin_zeit_start`) AND TIMESTAMP(`t4`.`termin_datum_ende`,`t4`.`termin_zeit_ende`))
                )'
            );

        $cte3->select(
            'c1.termin_id',
            "GROUP_CONCAT(DISTINCT `c1`.`termin_status`, '---', `c1`.`termin_betreff`, '---', `c1`.`termin_mitvon`, '---', `c1`.`termin_id`, '---', `c1`.`termin_datum_start`, '---', `c1`.`termin_datum_ende` ORDER BY `c1`.`termin_betreff` SEPARATOR '+++') AS _fehlbuchung"
        )
            ->from('tajo1_termin', 'c1')
            ->leftJoin(
                'c1',
                'cte1',
                't4',
                "
                `c1`.`termin_ist_konfliktrelevant` = 1 AND
                `c1`.`termin_ist_geloescht` = 0 AND
                `c1`.`termin_id` <> `t4`.`termin_id` AND 
                (
                        (TIMESTAMP(`t4`.`termin_datum_start`, `t4`.`termin_zeit_start`) BETWEEN TIMESTAMP(`c1`.`termin_datum_start`,`c1`.`termin_zeit_start`) AND TIMESTAMP(`c1`.`termin_datum_ende`,`c1`.`termin_zeit_ende`)) 
                        OR
                        (TIMESTAMP(`t4`.`termin_datum_ende`, `t4`.`termin_zeit_ende`) BETWEEN TIMESTAMP(`c1`.`termin_datum_start`,`c1`.`termin_zeit_start`) AND TIMESTAMP(`c1`.`termin_datum_ende`,`c1`.`termin_zeit_ende`))
                        OR
                        (TIMESTAMP(`c1`.`termin_datum_start`, `c1`.`termin_zeit_start`) BETWEEN TIMESTAMP(`t4`.`termin_datum_start`,`t4`.`termin_zeit_start`) AND TIMESTAMP(`t4`.`termin_datum_ende`,`t4`.`termin_zeit_ende`)) 
                        OR
                        (TIMESTAMP(`c1`.`termin_datum_ende`, `c1`.`termin_zeit_ende`) BETWEEN TIMESTAMP(`t4`.`termin_datum_start`,`t4`.`termin_zeit_start`) AND TIMESTAMP(`t4`.`termin_datum_ende`,`t4`.`termin_zeit_ende`))
                ) AND 
                (
                    `c1`.`termin_status` = :p3 AND
                    (
                        `c1`.`termin_mitvon` IS NULL 
                        OR `c1`.`termin_mitvon` = '' 
                        OR `t4`.`termin_mitvon` LIKE CONCAT('%',`c1`.`termin_mitvon`,'%')
                    )
                )"
            );

        $qb = $this->dbalConnection->createQueryBuilder();
        $qb->with('cte1', $cte1);
        $qb->with('cte2', $cte2);
        $qb->with('cte3', $cte3);
        $qb->select(
            't3.datum_id',
            't3.datum_datum',
            't4.termin_id',
            't4.termin_status',
            't4.termin_datum_start',
            't4.termin_datum_ende',
            't4.termin_zeit_ganztags',
            't4.termin_betreff',
            't4.termin_text',
            't4.termin_kategorie',
            't4.termin_mitvon',
            't4.termin_image',
            't4.termin_link',
            't4.termin_link_titel',
            't4.termin_link2',
            't4.termin_link2_titel',
            't4.termin_zeige_konflikt',
            't4.termin_zeige_einmalig',
            't4.termin_zeige_tagezuvor',
            't4.termin_aktiviere_drucken',
            't4.termin_ansicht',
            't4.termin_ist_konfliktrelevant',
            't4.termin_notiz',
            't4.termin_erstellt_am',
            't4.termin_aktualisiert_am',
            't4.termin_aktualisiert_am_trigger',
            'k._konflikt',
            'f._fehlbuchung'
        )
            ->addSelect('IF(t4.termin_datum_start = t3.datum_datum, t4.termin_zeit_start, null) AS termin_zeit_start')
            ->addSelect('IF(t4.termin_datum_ende = t3.datum_datum, t4.termin_zeit_ende, null) AS termin_zeit_ende')
            ->addSelect('(DATEDIFF(termin_datum_ende, termin_datum_start) + 1) AS _anzahl_tage')
            ->addSelect('UNIX_TIMESTAMP(DATE_FORMAT(termin_datum_start, "%Y-%m-%d 00:00:00")) AS _tag_start_uts')
            ->addSelect('UNIX_TIMESTAMP(DATE_FORMAT(termin_datum_ende, "%Y-%m-%d 00:00:00")) AS _tag_ende_uts')
            ->addSelect('IF(:p1 > DATEDIFF(CURDATE(), termin_erstellt_am), 1, 0) AS _is_new')
            ->addSelect('IF(:p2 > DATEDIFF(CURDATE(), termin_aktualisiert_am_trigger), 1, 0) AS _is_updated')
            ->from('tajo1_datum', 't3')
            ->leftJoin('t3', 'cte1', 't4', $this->getTerminJoinCondition($qb, $params))
            ->leftJoin('t4', 'cte2', 'k', 't4.termin_id = k.termin_id')
            ->leftJoin('t4', 'cte3', 'f', 't4.termin_id = f.termin_id')
            ->where((string)$this->getWhereCondition($qb, $params))
            ->groupBy(implode(",", $groupBy))
            ->orderBy($order)
            ->setParameter('p1', $initConfig['considered_as_new'])
            ->setParameter('p2', $initConfig['considered_as_updated'])
            ->setParameter('p3', TerminStatusEnum::WARNUNG->value);

        // limit
        if (!empty($params['limit'])) {
            $qb->setFirstResult(0)
                ->setMaxResults($params['limit']);
        }

        return $qb->fetchAllAssociative();
    }

    public function fetchMitvon(array $params = [], string $order = 't4.termin_mitvon ASC'): array
    {
        $qb = $this->dbalConnection->createQueryBuilder();
        $qb->select('t4.termin_mitvon')
            ->from('tajo1_datum', 't3')
            ->leftJoin(
                't3',
                '(SELECT `t1`.*,`t2`.`datum_id` FROM `tajo1_lnk_datum_termin` AS `t2` LEFT JOIN `tajo1_termin` AS `t1` ON `t1`.`termin_id` = `t2`.`termin_id`)',
                't4',
                $this->getTerminJoinCondition($qb, $params)
            )
            ->andWhere(
                $qb->expr()->isNotNull('t4.termin_mitvon'),
                $qb->expr()->neq('t4.termin_mitvon', "''")
            )
            ->groupBy('t4.termin_mitvon')
            ->orderBy($order);

        // where
        $where = $this->getWhereCondition($qb, $params);

        if (!empty($where))
            $qb->andWhere($where);

        // return
        return $qb->fetchAllAssociative();
    }

    public function fetchKategorie(array $params = [], string $order = 'termin_kategorie ASC'): array
    {
        $qb = $this->dbalConnection->createQueryBuilder();
        $qb->select('t4.termin_kategorie')
            ->from('tajo1_datum', 't3')
            ->leftJoin(
                't3',
                '(SELECT `t1`.*,`t2`.`datum_id` FROM `tajo1_lnk_datum_termin` AS `t2` LEFT JOIN `tajo1_termin` AS `t1` ON `t1`.`termin_id` = `t2`.`termin_id`)',
                't4',
                $this->getTerminJoinCondition($qb, $params)
            )
            ->andWhere(
                $qb->expr()->isNotNull('t4.termin_kategorie'),
                $qb->expr()->neq('t4.termin_kategorie', "''")
            )
            ->groupBy('t4.termin_kategorie')
            ->orderBy($order);

        // where
        $where = $this->getWhereCondition($qb, $params);

        if (!empty($where))
            $qb->andWhere($where);

        // return
        return $qb->fetchAllAssociative();
    }

    public function fetchBetreff(array $params = [], string $order = 'termin_betreff ASC'): array
    {
        $qb = $this->dbalConnection->createQueryBuilder();
        $qb->select('t4.termin_betreff')
            ->from('tajo1_datum', 't3')
            ->leftJoin(
                't3',
                '(SELECT `t1`.*,`t2`.`datum_id` FROM `tajo1_lnk_datum_termin` AS `t2` LEFT JOIN `tajo1_termin` AS `t1` ON `t1`.`termin_id` = `t2`.`termin_id`)',
                't4',
                $this->getTerminJoinCondition($qb, $params)
            )
            ->andWhere(
                $qb->expr()->isNotNull('t4.termin_betreff'),
                $qb->expr()->neq('t4.termin_betreff', "''")
            )
            ->groupBy('t4.termin_betreff')
            ->orderBy($order);

        // where
        $where = $this->getWhereCondition($qb, $params);

        if (!empty($where))
            $qb->andWhere($where);

        // return
        return $qb->fetchAllAssociative();
    }

    public function fetchLink(array $params = [], string $order = 'termin_link ASC'): array
    {
        $qb = $this->dbalConnection->createQueryBuilder();
        $qb->select('t4.termin_link')
            ->from('tajo1_datum', 't3')
            ->leftJoin(
                't3',
                '(SELECT `t1`.*,`t2`.`datum_id` FROM `tajo1_lnk_datum_termin` AS `t2` LEFT JOIN `tajo1_termin` AS `t1` ON `t1`.`termin_id` = `t2`.`termin_id`)',
                't4',
                $this->getTerminJoinCondition($qb, $params)
            )
            ->andWhere(
                $qb->expr()->isNotNull('t4.termin_link'),
                $qb->expr()->neq('t4.termin_link', "''")
            )
            ->groupBy('t4.termin_link')
            ->orderBy($order);

        // where
        $where = $this->getWhereCondition($qb, $params);

        if (!empty($where))
            $qb->andWhere($where);

        // return
        return $qb->fetchAllAssociative();
    }

    public function fetchLinkTitel(array $params = [], string $order = 'termin_link_titel ASC'): array
    {
        $qb = $this->dbalConnection->createQueryBuilder();
        $qb->select('t4.termin_link_titel')
            ->from('tajo1_datum', 't3')
            ->leftJoin(
                't3',
                '(SELECT `t1`.*,`t2`.`datum_id` FROM `tajo1_lnk_datum_termin` AS `t2` LEFT JOIN `tajo1_termin` AS `t1` ON `t1`.`termin_id` = `t2`.`termin_id`)',
                't4',
                $this->getTerminJoinCondition($qb, $params)
            )
            ->andWhere(
                $qb->expr()->isNotNull('t4.termin_link_titel'),
                $qb->expr()->neq('t4.termin_link_titel', "''")
            )
            ->groupBy('t4.termin_link_titel')
            ->orderBy($order);

        // where
        $where = $this->getWhereCondition($qb, $params);

        if (!empty($where))
            $qb->andWhere($where);

        // return
        return $qb->fetchAllAssociative();
    }

    public function fetchImage(array $params = [], string $order = 'termin_image ASC'): array
    {
        $qb = $this->dbalConnection->createQueryBuilder();
        $qb->select(
            'CONCAT(
                `t4`.`termin_image`, 
                IF(`t5`.`media_anzeige` IS NOT NULL AND `t5`.`media_anzeige` <> "", 
                    CONCAT(" -> ", `t5`.`media_anzeige`), 
                    IF(`t5`.`media_name` IS NOT NULL AND `t5`.`media_name` <> "", 
                        CONCAT(" -> ", `t5`.`media_name`),
                         ""
                    )
                )
            )',
            't4.termin_image',
            't5.media_name',
            't5.media_anzeige'
        )
            ->from('tajo1_datum', 't3')
            ->leftJoin(
                't3',
                '(SELECT `t1`.*,`t2`.`datum_id` FROM `tajo1_lnk_datum_termin` AS `t2` LEFT JOIN `tajo1_termin` AS `t1` ON `t1`.`termin_id` = `t2`.`termin_id`)',
                't4',
                $this->getTerminJoinCondition($qb, $params)
            )
            ->leftJoin(
                't4',
                'tajo1_media',
                't5',
                'CONCAT("/media/", `t5`.`media_id`) = `t4`.`termin_image`'
            )
            ->andWhere(
                $qb->expr()->isNotNull('t4.termin_image'),
                $qb->expr()->neq('t4.termin_image', "''")
            )
            ->groupBy('t4.termin_image')
            ->orderBy($order);

        // where
        $where = $this->getWhereCondition($qb, $params);

        if (!empty($where))
            $qb->andWhere($where);

        // return
        return $qb->fetchAllAssociative();
    }
}
