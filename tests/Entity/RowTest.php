<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Row;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Row::class)]
final class RowTest extends TestCase
{
    #[Test]
    public function itExposesGettersAndSetters(): void
    {
        $row = new Row();

        self::assertNull($row->getId());

        self::assertSame($row, $row->setTitle('Row A'));
        self::assertSame($row, $row->setRowNumber(3));

        self::assertSame('Row A', $row->getTitle());
        self::assertSame(3, $row->getRowNumber());
    }
}
