<?php

declare(strict_types=1);

namespace AppTest;

use App\Service\HelperService;
use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class HelperServiceTest extends TestCase
{
    public function testFormatFilesizeDefaultByte()
    {
        $this->assertSame('1024 Byte', HelperService::format_filesize(1024));
        $this->assertSame('1 KB', HelperService::format_filesize(1024, HelperService::CONST_UNIT_KB));
        $this->assertSame('1 MB', HelperService::format_filesize(1024 * 1024, HelperService::CONST_UNIT_MB));
        $this->assertSame('1 GB', HelperService::format_filesize(1024 * 1024 * 1024, HelperService::CONST_UNIT_GB));
    }

    public function testFormatDisplayDateSingle()
    {
        $date = new DateTimeImmutable('2021-03-15');
        $formatted = HelperService::format_displayDate($date, null, ['type' => HelperService::CONST_FORMAT_DATE_TYPE_DATETIME]);
        $this->assertSame($date->format('j.n.Y'), $formatted);

        // using DTF type - we can't rely on DTF output format, but ensure no exception when passing DateTime
        $formattedDtf = HelperService::format_displayDate($date, null, ['type' => HelperService::CONST_FORMAT_DATE_TYPE_DTF]);
        $this->assertIsString($formattedDtf);
    }

    public function testFormatDisplayDateRangeSameMonth()
    {
        $start = new DateTimeImmutable('2021-03-10');
        $end = new DateTimeImmutable('2021-03-20');

        $res = HelperService::format_displayDate($start, $end, ['type' => HelperService::CONST_FORMAT_DATE_TYPE_DATETIME]);
        $this->assertStringContainsString(' - ', $res);
        // when both dates are in the same month the left part should be a day only (e.g. "10")
        $this->assertStringContainsString($start->format('j'), $res);
        // the right part should contain the full date
        $this->assertStringContainsString($end->format('j.n.Y'), $res);
    }

    public function testFormatDisplayTime()
    {
        $this->assertSame('08:30 Uhr', HelperService::format_displayTime('08:30'));
        $this->assertSame('08:30 bis 09:45 Uhr', HelperService::format_displayTime('08:30', '09:45'));
    }

    public function testStringSubstrWords()
    {
        $str = 'The quick brown fox jumps over the lazy dog';
        $res = HelperService::string_substrWords($str, 10, 3);
        $this->assertStringStartsWith('The quick', $res);
        $this->assertStringEndsWith(' ...', $res);

        $short = 'Hello world';
        $this->assertSame('Hello world', HelperService::string_substrWords($short, 50));
    }

    public function testGetBrowserCacheHeaders()
    {
        $headers = HelperService::getBrowserCacheHeaders(3600);
        $this->assertArrayHasKey('Cache-Control', $headers);
        $this->assertArrayHasKey('Expires', $headers);
        $this->assertArrayHasKey('Last-Modified', $headers);
        $this->assertStringContainsString('max-age=3600', $headers['Cache-Control']);
    }

    public function testAttributeAnchorHelpers()
    {
        $this->assertSame('data-test="value"', HelperService::getAttribute('data-test', 'value'));
        $this->assertSame('id="anchor-42"', HelperService::getAnchorAttribute(42));
        $this->assertSame('', HelperService::getAnchorAttribute(false));
        $this->assertSame('anchor-42', HelperService::getAnchorString(42));
    }

    public function testDateValidators()
    {
        $this->assertInstanceOf(DateTime::class, HelperService::getValidDate('2021-12-31'));
        $this->assertFalse(HelperService::getValidDate('2021-13-31'));
        $this->assertInstanceOf(DateTimeImmutable::class, HelperService::getValidDateImmutable('2021-12-31'));
        $this->assertFalse(HelperService::getValidDateImmutable('2021-13-31'));

        $this->assertInstanceOf(DateTime::class, HelperService::getValidTime('08:30'));
        $this->assertFalse(HelperService::getValidTime('25:00'));
        $this->assertInstanceOf(DateTimeImmutable::class, HelperService::getValidTimeImmutable('08:30'));
        $this->assertFalse(HelperService::getValidTimeImmutable('25:00'));
    }

    public function testMonthCalendarHelpers()
    {
        $first = HelperService::getMonthFirstDayForCalender('2021-03-15');
        $this->assertInstanceOf(DateTimeInterface::class, $first);
        $this->assertEquals(1, (int)$first->format('N'));

        $last = HelperService::getMonthLastDayForCalender('2021-03-15');
        $this->assertInstanceOf(DateTimeInterface::class, $last);
        $this->assertEquals(7, (int)$last->format('N'));
    }

    public function testGetSeriePeriod()
    {
    // use a concrete interval string that DateInterval::createFromDateString understands
    $period = HelperService::getSeriePeriod('2021-03-01', '2021-03-10', '1 day');
        $this->assertInstanceOf(DatePeriod::class, $period);
        $items = iterator_to_array($period);
        $this->assertNotEmpty($items);
    }

    public function testInvalidDatesThrow()
    {
        $this->expectException(InvalidArgumentException::class);
        HelperService::format_displayDate('invalid-date');
    }

    public function testInvalidTimeThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        HelperService::format_displayTime('invalid-time');
    }
}
