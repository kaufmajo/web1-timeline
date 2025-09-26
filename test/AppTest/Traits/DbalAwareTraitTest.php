<?php

declare(strict_types=1);

namespace AppTest\Traits;

use App\Traits\Aware\DbalAwareTrait;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class DummyDbal
{
    use DbalAwareTrait;

    public function get(): Connection
    {
        return $this->getDbalConnection();
    }
}

class DbalAwareTraitTest extends TestCase
{
    public function testSetAndGetDbalConnection(): void
    {
        $conn = $this->createMock(Connection::class);

        $dummy = new DummyDbal();
        $dummy->setDbalConnection($conn);

        $this->assertSame($conn, $dummy->get());
    }
}
