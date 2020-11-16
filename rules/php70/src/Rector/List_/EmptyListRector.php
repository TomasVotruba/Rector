<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\List_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;

/**
 * @source http://php.net/manual/en/migration70.incompatible.php#migration70.incompatible.variable-handling.list
 * @see \Rector\Php70\Tests\Rector\List_\EmptyListRector\EmptyListRectorTest
 */
final class EmptyListRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition(
            'list() cannot be empty',
            [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                <<<'CODE_SAMPLE'
'list() = $values;'
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
'list($unusedGenerated) = $values;'
CODE_SAMPLE
            )]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [List_::class];
    }

    /**
     * @param List_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($node->items as $item) {
            if ($item !== null) {
                return null;
            }
        }

        $node->items[0] = new ArrayItem(new Variable('unusedGenerated'));

        return $node;
    }
}
