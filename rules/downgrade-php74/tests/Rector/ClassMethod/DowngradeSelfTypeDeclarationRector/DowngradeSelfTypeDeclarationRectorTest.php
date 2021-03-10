<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector;

use Iterator;
use Rector\DowngradePhp74\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeSelfTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.4
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<mixed, SmartFileInfo[]>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return DowngradeSelfTypeDeclarationRector::class;
    }
}
