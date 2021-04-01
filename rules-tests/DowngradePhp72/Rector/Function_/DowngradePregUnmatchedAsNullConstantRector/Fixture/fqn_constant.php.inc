<?php

namespace Rector\Tests\DowngradePhp72\Rector\Function_\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class FqnConstant
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, $flags | \PREG_UNMATCHED_AS_NULL);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\Function_\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class FqnConstant
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, $flags);
        array_walk_recursive($matches, function (& $value) {
            if ($value === '') {
                $value = null;
            }
        });
    }
}

?>