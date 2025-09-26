<?php

declare(strict_types=1);

namespace AppTest\Traits;

use App\Traits\Aware\UrlHelperAwareTrait;
use Mezzio\Helper\UrlHelper;
use PHPUnit\Framework\TestCase;

class DummyUrl
{
    use UrlHelperAwareTrait;

    public function get(): UrlHelper
    {
        return $this->getUrlHelper();
    }
}

class UrlHelperAwareTraitTest extends TestCase
{
    public function testSetAndGetUrlHelper(): void
    {
        $helper = $this->createMock(UrlHelper::class);

        $d = new DummyUrl();
        $d->setUrlHelper($helper);

        $this->assertSame($helper, $d->get());
    }
}
