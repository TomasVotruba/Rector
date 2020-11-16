<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Catch_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use Rector\Core\Rector\AbstractRector;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Catch_\CatchExceptionNameMatchingTypeRector\CatchExceptionNameMatchingTypeRectorTest
 */
final class CatchExceptionNameMatchingTypeRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/xmfMAX/1
     */
    private const STARTS_WITH_ABBREVIATION_REGEX = '#^([A-Za-z]+?)([A-Z]{1}[a-z]{1})([A-Za-z]*)#';

    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition(
            'Type and name of catch exception should match',
            [
                new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            // ...
        } catch (SomeException $typoException) {
            $typoException->getMessage();
        }
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            // ...
        } catch (SomeException $someException) {
            $someException->getMessage();
        }
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
        return [Catch_::class];
    }

    /**
     * @param Catch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->types) !== 1) {
            return null;
        }

        if ($node->var === null) {
            return null;
        }

        $oldVariableName = $this->getName($node->var);
        if (! $oldVariableName) {
            return null;
        }

        $type = $node->types[0];
        $typeShortName = $this->getShortName($type);

        $newVariableName = Strings::replace(
            lcfirst($typeShortName),
            self::STARTS_WITH_ABBREVIATION_REGEX,
            function (array $matches): string {
                $output = '';

                $output .= isset($matches[1]) ? strtolower($matches[1]) : '';
                $output .= $matches[2] ?? '';
                $output .= $matches[3] ?? '';

                return $output;
            }
        );

        if ($oldVariableName === $newVariableName) {
            return null;
        }

        $node->var->name = $newVariableName;

        $this->renameVariableInStmts($node, $oldVariableName, $newVariableName);

        return $node;
    }

    private function renameVariableInStmts(Catch_ $catch, string $oldVariableName, string $newVariableName): void
    {
        $this->traverseNodesWithCallable($catch->stmts, function (Node $node) use (
            $oldVariableName,
            $newVariableName
        ): void {
            if (! $this->isVariableName($node, $oldVariableName)) {
                return;
            }

            $node->name = $newVariableName;
        });
    }
}
