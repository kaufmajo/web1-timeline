<?php

declare(strict_types=1);

namespace App\Handler\Home\Mng;

use App\Handler\AbstractBaseHandler;
use App\Traits\Aware\DbalAwareTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeStatsHandler extends AbstractBaseHandler
{
    use DbalAwareTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // param
        $groupByIp = (int) $request->getQueryParams()['gip'];
        $excludeIp = (string) ($request->getQueryParams()['exip'] ?? '');

        if ($excludeIp && ! (new Validator\Ip())->isValid($excludeIp)) {
            return new TextResponse('Filter IP is invalid.');
        }

        // init
        $dbalConnection = $this->getDbalConnection();

        $stats = []; // will contain: [$url => ['url', 'anzahl', ['ip']]]"

        // 
        $qb = $dbalConnection->createQueryBuilder();

        $qb->select('*')
            ->from('tajo1_history')
            ->orderBy('`tajo1_history`.`history_id` DESC');

        $data = $qb->fetchAllAssociative();

        // determine stats
        foreach ($data as $d) {
            if ($excludeIp && $d['history_ip'] === $excludeIp) {
                continue;
            }

            $url = parse_url((string)$d['history_url']);

            if (isset($url['path'])) {
                $parts = preg_split('/\//', $url['path'], -1, PREG_SPLIT_NO_EMPTY);

                if (count($parts) >= 2) {
                    $url['path'] = '/' . $parts[0] . (! is_numeric($parts[1]) ? '/' . $parts[1] : '');
                }
            }

            $index  = isset($url['scheme']) ? $url['scheme'] . '://' : '';
            $index .= isset($url['host']) ? $url['host'] . ':' : '';
            $index .= $url['port'] ?? '';
            $index .= $url['path'] ?? '';

            if (! isset($stats[$index])) {
                $stats[$index] = ['url' => $index, 'anzahl' => 1, 'ip' => [$d['history_ip']]];
            } elseif (0 === $groupByIp || ! in_array($d['history_ip'], $stats[$index]['ip'])) {
                $stats[$index]['anzahl'] = ++$stats[$index]['anzahl'];
                $stats[$index]['ip'][]   = $d['history_ip'];
            }
        }

        return new HtmlResponse(
            $this->templateRenderer->render('app::home/mng/stats', [
                'stats'        => $stats,
                'myInitConfig' => $this->getMyInitConfig(),
            ])
        );
    }
}
