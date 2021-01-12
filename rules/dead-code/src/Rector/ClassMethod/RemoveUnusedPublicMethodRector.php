<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPublicMethodRector\RemoveUnusedPublicMethodRectorTest
 */
final class RemoveUnusedPublicMethodRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var MethodCall[]|StaticCall[]|ArrayCallable[]
     */
    private $calls = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove unused public method',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function unusedpublicMethod()
    {
        // ...
    }

    public function execute()
    {
        // ...
    }

    public function run()
    {
        $obj = new self;
        $obj->execute();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function execute()
    {
        // ...
    }

    public function run()
    {
        $obj = new self;
        $obj->execute();
    }
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->isPublic()) {
            return null;
        }

        $this->calls = array_merge($this->calls, $this->nodeRepository->findCallsByClassMethod($node));
        dump($this->calls);
        if ($this->calls === []) {
            $this->removeNode($node);
            return $node;
        }

        $isFoundCall = (bool) $this->betterNodeFinder->findFirst($node->stmts, function (Node $n): bool {
            foreach ($this->calls as $call) {
                if ($this->areNodesEqual($call, $n)) {
                    return true;
                }

                return false;
            }
        });

        if ($isFoundCall) {
            $this->removeNode($node);
            return $node;
        }

        return null;
    }
}
