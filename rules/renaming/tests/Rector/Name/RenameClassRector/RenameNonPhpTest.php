<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector;

use Iterator;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameNonPhpTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(
            __DIR__ . '/FixtureRenameNonPhp',
            StaticNonPhpFileSuffixes::getSuffixRegexPattern()
        );
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/non_php_config.php';
    }
}
