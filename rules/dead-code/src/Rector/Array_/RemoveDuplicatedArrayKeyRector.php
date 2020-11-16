<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use Rector\Core\Rector\AbstractRector;

/**
 * @see https://3v4l.org/SG0Wu
 * @see \Rector\DeadCode\Tests\Rector\Array_\RemoveDuplicatedArrayKeyRector\RemoveDuplicatedArrayKeyRectorTest
 */
final class RemoveDuplicatedArrayKeyRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove duplicated key in defined arrays.', [
            new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                <<<'CODE_SAMPLE'
$item = [
    1 => 'A',
    1 => 'B'
];
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$item = [
    1 => 'B'
];
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Array_::class];
    }

    /**
     * @param Array_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $arrayItemsWithDuplicatedKey = $this->getArrayItemsWithDuplicatedKey($node);
        if ($arrayItemsWithDuplicatedKey === []) {
            return null;
        }

        foreach ($arrayItemsWithDuplicatedKey as $arrayItems) {
            // keep last item
            array_pop($arrayItems);
            $this->removeNodes($arrayItems);
        }

        return $node;
    }

    /**
     * @return ArrayItem[][]
     */
    private function getArrayItemsWithDuplicatedKey(Array_ $array): array
    {
        $arrayItemsByKeys = [];

        foreach ($array->items as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            if ($arrayItem->key === null) {
                continue;
            }

            $keyValue = $this->print($arrayItem->key);
            $arrayItemsByKeys[$keyValue][] = $arrayItem;
        }

        return $this->filterItemsWithSameKey($arrayItemsByKeys);
    }

    /**
     * @param ArrayItem[][] $arrayItemsByKeys
     * @return ArrayItem[][]
     */
    private function filterItemsWithSameKey(array $arrayItemsByKeys): array
    {
        $arrayItemsByKeys = array_filter($arrayItemsByKeys, function (array $arrayItems): bool {
            return count($arrayItems) > 1;
        });

        /** @var ArrayItem[][] $arrayItemsByKeys */
        return $arrayItemsByKeys;
    }
}
