<?php

declare(strict_types=1);

namespace Rector\Tests\Order\Rector\Class_\OrderPrivateMethodsByUseRector;

use Iterator;
use Rector\Order\Rector\Class_\OrderPrivateMethodsByUseRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OrderPrivateMethodsByUseRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return OrderPrivateMethodsByUseRector::class;
    }
}
