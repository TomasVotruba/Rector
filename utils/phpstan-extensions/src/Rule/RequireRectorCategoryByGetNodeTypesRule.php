<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use Rector\Core\Exception\ShouldNotHappenException;

/**
 * @see \Rector\PHPStanExtensions\Tests\Rule\KeepRectorNamespaceForRectorRule\KeepRectorNamespaceForRectorRuleTest
 */
final class RequireRectorCategoryByGetNodeTypesRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Class "%s" has invalid namespace category "%s". Pick one of: "%s"';

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $rectorClassReflection = $this->resolveRectorClassReflection($node, $scope);
        if ($rectorClassReflection === null) {
            return [];
        }

        $currentRectorCategory = $this->resolveRectorCategory($rectorClassReflection);

        $allowedRectorCategories = $this->resolveAllowedNodeCategories($node);
        if (in_array($currentRectorCategory, $allowedRectorCategories, true)) {
            return [];
        }

        $errorMessage = sprintf(
            self::ERROR_MESSAGE,
            $rectorClassReflection->getName(),
            $currentRectorCategory,
            implode('", ', $allowedRectorCategories)
        );

        return [$errorMessage];
    }

    private function resolveRectorCategory(ClassReflection $classReflection): string
    {
        $nameParts = explode('\\', $classReflection->getName());
        array_pop($nameParts);

        $categoryPart = array_pop($nameParts);
        if ($categoryPart === null) {
            throw new ShouldNotHappenException();
        }

        return $categoryPart;
    }

    /**
     * @return string[]
     */
    private function resolveAllowedNodeCategories(ClassMethod $classMethod): array
    {
        $nodeFinder = new NodeFinder();

        /** @var ClassConstFetch[] $classConstFetches */
        $classConstFetches = $nodeFinder->findInstanceOf((array) $classMethod->stmts, ClassConstFetch::class);
        $allowedRectorCategories = [];

        foreach ($classConstFetches as $classConstFetch) {
            if (! $classConstFetch->class instanceof FullyQualified) {
                continue;
            }

            $allowedRectorCategories[] = $classConstFetch->class->getLast();
        }

        // add parent class categories
        $functionLikeCandidateCount = 0;
        foreach ($allowedRectorCategories as $allowedRectorCategory) {
            if (! in_array($allowedRectorCategory, ['ClassMethod', 'Function_', 'Closure', 'ArrowFunction'], true)) {
                continue;
            }

            ++$functionLikeCandidateCount;
        }

        if ($functionLikeCandidateCount > 1) {
            $allowedRectorCategories[] = 'FunctionLike';
        }

        return $allowedRectorCategories;
    }

    private function resolveRectorClassReflection(ClassMethod $classMethod, Scope $scope): ?ClassReflection
    {
        if ($classMethod->name->toString() !== 'getNodeTypes') {
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return null;
        }

        if ($classReflection->isInterface()) {
            return null;
        }

        if ($classReflection->isAbstract()) {
            return null;
        }

        return $classReflection;
    }
}
