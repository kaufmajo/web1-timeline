<?php

declare(strict_types=1);

namespace App\Service;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use JK\DTF\DTF;

use function explode;
use function gmdate;
use function ini_get;
use function PHPUnit\Framework\isNull;
use function round;
use function strlen;
use function substr;
use function time;

class HelperService
{
    const CONST_UNIT_KB   = 'KB';
    const CONST_UNIT_MB   = 'MB';
    const CONST_UNIT_GB   = 'GB';
    const CONST_UNIT_TB   = 'TB';
    const CONST_UNIT_BYTE = 'Byte';

    const CONST_FORMAT_DATE_TYPE_DTF = 'dtf';
    const CONST_FORMAT_DATE_TYPE_DATETIME = 'datetime';

    // -----------------------------------------------
    //
    //
    // format
    //
    //
    //-----------------------------------------------

    public static function format_filesize(int $bytes, string $unit = self::CONST_UNIT_BYTE): string
    {
        $units       = ['KB', 'MB', 'GB', 'TB'];
        $unit_suffix = self::CONST_UNIT_BYTE;

        if ($unit !== $unit_suffix) {
            for ($i = 0; $bytes >= 1024 && $i < 3; $i++) {
                $bytes      /= 1024;
                $unit_suffix = $units[$i];

                if ($unit === $units[$i]) {
                    break;
                }
            }
        }

        return round($bytes, 2) . ' ' . $unit_suffix;
    }

    public static function format_displayDate(string|DateTimeInterface $datumStart, null|string|DateTimeInterface $datumEnde = null, ?array $options = [
        'type' => self::CONST_FORMAT_DATE_TYPE_DATETIME,
    ]): string
    {
        if (is_string($datumStart) && ! ($datumStart = self::getValidDate($datumStart))) {
            throw new InvalidArgumentException("Given datestring '$datumStart' is invalid");
        }

        if (is_string($datumEnde) && ! ($datumEnde = self::getValidDate($datumEnde))) {
            throw new InvalidArgumentException("Given datestring '$datumEnde' is invalid");
        }

        // init options
        $type   = $options['type'] ?? self::CONST_FORMAT_DATE_TYPE_DTF;

        if (self::CONST_FORMAT_DATE_TYPE_DATETIME === $type) {
            $single = $options['single'] ?? 'j.n.Y';
            $left  = $options['left'] ?? 'j[.n.Y]';
            $right = $options['right'] ?? 'j.n.Y';
        } else {
            $single = $options['single'] ?? 'd MMMM yy';
            $left  = $options['left'] ?? 'd [MMMM yy]';
            $right = $options['right'] ?? 'd MMMM yy';
        }

        // process
        if (null === $datumEnde || $datumEnde == $datumStart) {
            return $type === self::CONST_FORMAT_DATE_TYPE_DTF
                ? DTF::format($datumStart, $single)
                : $datumStart->format($single);
        } else {
            if ($datumStart->format('Y') === $datumEnde->format('Y') && $datumStart->format('n') === $datumEnde->format('n')) {
                $left =  substr_replace($left, '', strpos($left, '['), strpos($left, ']') - strpos($left, '[') + 1);
            } else {
                $left =  substr_replace($left, '', strpos($left, '['),  1);
                $left =  substr_replace($left, '', strpos($left, ']'),  1);
            }

            return $type === self::CONST_FORMAT_DATE_TYPE_DTF
                ? DTF::format($datumStart, $left) . ' - ' . DTF::format($datumEnde, $right)
                : $datumStart->format($left) . ' - ' . $datumEnde->format($right);
        }
    }

    public static function format_displayTime(string $zeitStart, string $zeitEnde = ''): string
    {
        if (! ($zeitStartDateTime = self::getValidTime($zeitStart))) {
            throw new InvalidArgumentException("Given timestring '$zeitStart' is invalid");
        }

        if ('' !== $zeitEnde && ! ($zeitEndeDateTime = self::getValidTime($zeitEnde))) {
            throw new InvalidArgumentException("Given timestring '$zeitEnde' is invalid");
        }

        $return = $zeitStartDateTime->format('H:i');

        if ('' === $zeitEnde) {
            $return .= ' Uhr';
        } else {
            $return .= ' bis ' . $zeitEndeDateTime->format('H:i') . ' Uhr';
        }

        return $return;
    }

    // ----------------------------------------------
    //
    //
    // strings
    //
    //
    //-----------------------------------------------

    /**
     * Substring without losing word meaning and
     * tiny words (length 3 by default) are included on the result.
     * "..." is added if result do not reach original string length
     */
    public static function string_substrWords(string $str, int $length, int $allowLastWordCharactersCount = 3): string
    {
        $newStr = '';

        foreach (explode(' ', $str) as $word) {
            $newStr .= ($newStr !== '' ? ' ' : '') . $word;

            if (strlen($word) > $allowLastWordCharactersCount && strlen($newStr) >= $length) {
                break;
            }
        }

        return $newStr . (strlen($newStr) < strlen($str) ? ' ...' : '');
    }

    // ----------------------------------------------
    //
    //
    // cache
    //
    //
    //-----------------------------------------------

    public static function getBrowserCacheHeaders(int $lifetime): array
    {
        return [
            'Cache-Control' => 'private, must-revalidate, max-age=' . $lifetime,
            'Expires'       => gmdate('D, d M Y H:i:s', time() + $lifetime) . ' GMT',
            'Last-Modified' => gmdate('D, d M Y H:i:s', time()) . ' GMT',
        ];
    }

    // ----------------------------------------------
    //
    //
    // array
    //
    //
    //-----------------------------------------------

    // ----------------------------------------------
    //
    //
    // File
    //
    //
    //-----------------------------------------------

    public static function isPostMaxSizeReached(): void
    {
        // check that post_max_size has not been reached
        if (
            $_SERVER['REQUEST_METHOD'] == 'POST' && $_POST === [] &&
            $_FILES === [] && $_SERVER['CONTENT_LENGTH'] > 0
        ) {
            $displayMaxSize = ini_get('post_max_size');

            switch (substr($displayMaxSize, -1)) {
                case 'G':
                    $displayMaxSize *= 1024;
                case 'M':
                    $displayMaxSize *= 1024;
                case 'K':
                    $displayMaxSize *= 1024;
            }

            $error = 'Posted data is too large. '
                . $_SERVER['CONTENT_LENGTH']
                . ' bytes exceeds the maximum size of '
                . $displayMaxSize . ' bytes.';

            die($error);
        }
    }

    // ----------------------------------------------
    //
    //
    // Attribute / Anchor
    //
    //
    //-----------------------------------------------

    public static function getAttribute(string $attribute, string|array $value): string
    {
        $value = is_array($value) ? $value : [$value];

        return $attribute . '="' . implode("", $value) . '"';
    }

    public static function getAnchorAttribute(int|string|false $anchor): string
    {
        return $anchor ? 'id="anchor-' . $anchor . '"' : '';
    }

    public static function getAnchorString(int|string|false $anchor): string
    {
        return $anchor ? 'anchor-' . $anchor : '';
    }

    // ----------------------------------------------
    //
    //
    // date
    //
    //
    //-----------------------------------------------

    public static function getValidDate($date, $format = 'Y-m-d'): false|DateTime
    {
        $dt = DateTime::createFromFormat($format, $date);
        return ($dt && $dt->format($format) === $date) ? $dt : false;
    }

    public static function getValidDateImmutable($date, $format = 'Y-m-d'): false|DateTimeImmutable
    {
        $dt = DateTimeImmutable::createFromFormat($format, $date);
        return ($dt && $dt->format($format) === $date) ? $dt : false;
    }

    public static function getValidTime($time, $format = 'H:i'): false|DateTime
    {
        $dt = DateTime::createFromFormat($format, $time);
        return ($dt && $dt->format($format) === $time) ? $dt : false;
    }

    public static function getValidTimeImmutable($time, $format = 'H:i'): false|DateTimeImmutable
    {
        $dt = DateTimeImmutable::createFromFormat($format, $time);
        return ($dt && $dt->format($format) === $time) ? $dt : false;
    }

    public static function getMonthFirstDayForCalender(DateTime|string $date): DateTimeInterface
    {
        if (is_string($date) && !($dt = self::getValidDateImmutable($date))) {
            throw new InvalidArgumentException("Given datestring '$date' is invalid");
        }

        $start = $dt->modify('first day of this month');

        // is start day a monday
        return 1 === (int)$start->format('N') ? $start : $start->modify('last monday of last month');
    }

    public static function getMonthLastDayForCalender(DateTime|string $date): DateTimeInterface
    {
        if (is_string($date) && !($dt = self::getValidDateImmutable($date))) {
            throw new InvalidArgumentException("Given datestring '$date' is invalid");
        }

        $ende = $dt->modify('last day of this month');

        // is start day a monday
        return 7 === (int)$ende->format('N') ? $ende : $ende->modify('first sunday of next month');
    }

    public static function getSeriePeriod(string $startDatum, string $serienEnde, string $wiederholung): DatePeriod
    {
        return new DatePeriod(
            new DateTime($startDatum),
            DateInterval::createFromDateString(str_replace('[day]', (new DateTime($startDatum))->format('l'), $wiederholung)),
            new DateTime($serienEnde)
        );
    }
}
