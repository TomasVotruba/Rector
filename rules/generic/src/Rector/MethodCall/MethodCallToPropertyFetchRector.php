<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Naming\PropertyNaming;

/**
 * @see \Rector\Generic\Tests\Rector\MethodCall\MethodCallToPropertyFetchRector\MethodCallToPropertyFetchRectorTest
 */
final class MethodCallToPropertyFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const METHOD_CALL_TO_PROPERTY_FETCHES = '$methodCallToPropertyFetchCollection';

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var array<string, string>
     */
    private $methodCallToPropertyFetchCollection = [];

    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns method call "$this->something()" to property fetch "$this->something"', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->someMethod();
    }
}
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->someProperty;
    }
}
CODE_SAMPLE
                ,
                [
                    self::METHOD_CALL_TO_PROPERTY_FETCHES => [
                        'someMethod' => 'someProperty',
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->methodCallToPropertyFetchCollection as $methodName => $propertyName) {
            if (! $this->isName($node->name, $methodName)) {
                continue;
            }

            if ($propertyName === null) {
                return new Variable('this');
            }

            return $this->createPropertyFetch('this', $propertyName);
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->methodCallToPropertyFetchCollection = $configuration[self::METHOD_CALL_TO_PROPERTY_FETCHES] ?? [];
    }
}
