<?php

declare(strict_types=1);

namespace Rector\Php53\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\Ternary;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see http://php.net/manual/en/language.operators.comparison.php#language.operators.comparison.ternary
 * @see https://stackoverflow.com/a/1993455/1348344
 * @see \Rector\Php53\Tests\Rector\Ternary\TernaryToElvisRector\TernaryToElvisRectorTest
 */
final class TernaryToElvisRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use ?: instead of ?, where useful', [
            new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                <<<'CODE_SAMPLE'
function elvis()
{
    $value = $a ? $a : false;
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
function elvis()
{
    $value = $a ?: false;
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Ternary::class];
    }

    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::ELVIS_OPERATOR)) {
            return null;
        }

        if (! $this->areNodesEqual($node->cond, $node->if)) {
            return null;
        }

        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $node->if = null;

        return $node;
    }
}
