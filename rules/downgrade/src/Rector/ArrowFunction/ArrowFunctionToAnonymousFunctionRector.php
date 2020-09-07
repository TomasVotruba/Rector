<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\ArrowFunction;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\AbstractRector\Rector\AbstractConvertToAnonymousFunctionRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://www.php.net/manual/en/functions.arrow.php
 *
 * @see \Rector\Downgrade\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\ArrowFunctionToAnonymousFunctionRectorTest
 */
final class ArrowFunctionToAnonymousFunctionRector extends AbstractConvertToAnonymousFunctionRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace arrow functions with anonymous functions', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $delimiter = ",";
        $callable = fn($matches) => $delimiter . strtolower($matches[1]);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $delimiter = ",";
        $callable = function ($matches) use ($delimiter) {
            return $delimiter . strtolower($matches[1]);
        };
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ArrowFunction::class];
    }

    /**
     * @param ArrowFunction $node
     */
    protected function shouldSkip(Node $node): bool
    {
        return false;
    }

    /**
     * @param ArrowFunction $node
     * @return Variable[]
     */
    protected function getParameters(Node $node): array
    {
        /** @var Variable[] */
        return $node->params;
    }

    /**
     * @param ArrowFunction $node
     * @return Expression[]|Stmt[]
     */
    protected function getBody(Node $node): array
    {
        return [new Return_($node->expr)];
    }
}
