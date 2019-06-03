<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector;

use Rector\Php\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringClassNameToClassConstantRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/skip_error.php.inc',
            __DIR__ . '/Fixture/ignore_lowercase_fully_qualified.php.inc',
            __DIR__ . '/Fixture/transform_array_keys_if_fully_qualified.php.inc',
            __DIR__ . '/Fixture/ignore_array_keys.php.inc',
            __DIR__ . '/Fixture/ignore_lowercase_strings.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return StringClassNameToClassConstantRector::class;
    }
}
