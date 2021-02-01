<?php

namespace Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\NowString as NowStringAlias;

final class SomeAliasedClass
{
    public function run(NowStringAlias $nowString)
    {
        $nowString->setValue(null);
    }
}

?>
    -----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\NowString as NowStringAlias;

final class SomeAliasedClass
{
    public function run(NowStringAlias $nowString)
    {
        $nowString->setValue('');
    }
}

?>