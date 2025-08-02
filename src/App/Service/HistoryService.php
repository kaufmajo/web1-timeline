<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\DBAL\Connection;

class HistoryService
{
    protected const CONST_THROTTLE       = 'Throttle';
    protected const CONST_THROTTLE_COUNT = 7;

    protected Connection $dbal;

    public function __construct(Connection $dbal)
    {
        $this->dbal = $dbal;
    }

    public function getUrlString(): string
    {
        $url  = isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] ? 'https://' : 'http://';
        $url .= $_SERVER['SERVER_NAME'];
        $url .= ':' . $_SERVER['SERVER_PORT'];
        $url .= $_SERVER['REQUEST_URI'];

        return $url;
    }

    public function insertRow(): static
    {
        $url = $this->getUrlString();

        $sql = "INSERT INTO `tajo1_history` (`history_url`, `history_ip`) VALUES (?, ?)";

        $this->dbal->executeStatement($sql, [$url, $_SERVER["REMOTE_ADDR"]]);

        return $this;
    }

    public function insertThrottleRow(string $identifier = ''): static
    {
        $identifier = $identifier ? '_' . $identifier : $identifier;

        $url = self::CONST_THROTTLE;

        $sql = "INSERT INTO `tajo1_history` (`history_url`, `history_ip`) VALUES (?, ?)";

        $this->dbal->executeStatement($sql, [$url, $_SERVER["REMOTE_ADDR"] . $identifier]);

        return $this;
    }

    public function throttle(string $identifier = ''): void
    {
        $identifier = $identifier ? '_' . $identifier : $identifier;

        $sql = "
              SELECT
                    COUNT(`history_url`) AS `Anzahl`
              FROM
                    `tajo1_history`
              WHERE
                    `history_timestamp` > DATE_SUB(NOW(), INTERVAL 60 MINUTE)
                    AND `history_ip` = ? 
                    AND `history_url` = ?
              GROUP BY
                    `history_ip`,
                    `history_url`";

        $url = self::CONST_THROTTLE;

        $resultSet = $this->dbal->executeQuery($sql, [$_SERVER["REMOTE_ADDR"] . $identifier, $url]);

        $count = $resultSet->fetchOne();

        if ((self::CONST_THROTTLE_COUNT + 1) < $count) {
            exit;
        } elseif (self::CONST_THROTTLE_COUNT < $count) {
            exit('Trying output ...'); // exit('Throttle für IP ' . $_SERVER['REMOTE_ADDR'] . ' aktiv.');
        }
    }
}
