<?php

declare(strict_types=1);

namespace AppTest\Traits;

use App\Traits\Aware\ConfigAwareTrait;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DummyConfig
{
    use ConfigAwareTrait;

    public function getCfg(?string $k = null): array
    {
        return $this->getConfig($k);
    }

    public function getMy(?string $k = null): array
    {
        return $this->getMyInitConfig($k);
    }
}

class ConfigAwareTraitTest extends TestCase
{
    public function testSetAndGetConfig(): void
    {
        $cfg = ['foo' => ['bar' => 1], 'my_init_config' => ['x' => 'y']];

        $d = new DummyConfig();
        $d->setConfig($cfg);

        $this->assertSame($cfg, $d->getCfg(null));
        $this->assertSame($cfg['foo'], $d->getCfg('foo'));

        // my_init_config fallback
        $this->assertSame($cfg['my_init_config'], $d->getMy(null));
    }

    public function testGetConfigThrowsForMissingKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $d = new DummyConfig();
        $d->setConfig([]);
        $d->getCfg('missing');
    }

    public function testGetMyInitConfigThrowsForMissingKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $d = new DummyConfig();
        $d->setMyInitConfig([]);
        $d->getMy('nope');
    }
}
