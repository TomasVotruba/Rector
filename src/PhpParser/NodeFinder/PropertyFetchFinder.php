<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class PropertyFetchFinder
{
    /**
     * @var \Rector\NodeCollector\NodeCollector\NodeRepository
     */
    private $nodeRepository;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeRepository = $nodeRepository;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return PropertyFetch[]|StaticPropertyFetch[]
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $propertyOrPromotedParam
     */
    public function findPrivatePropertyFetches($propertyOrPromotedParam) : array
    {
        $classLike = $propertyOrPromotedParam->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return [];
        }
        $classLikesToSearch = $this->nodeRepository->findUsedTraitsInClass($classLike);
        $classLikesToSearch[] = $classLike;
        $propertyName = $this->resolvePropertyName($propertyOrPromotedParam);
        if ($propertyName === null) {
            return [];
        }
        /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->find($classLikesToSearch, function (\PhpParser\Node $node) use($propertyName, $classLikesToSearch) : bool {
            // property + static fetch
            if (!$node instanceof \PhpParser\Node\Expr\PropertyFetch && !$node instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
                return \false;
            }
            // is it the name match?
            if (!$this->nodeNameResolver->isName($node, $propertyName)) {
                return \false;
            }
            $currentClassLike = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
            return \in_array($currentClassLike, $classLikesToSearch, \true);
        });
        return $propertyFetches;
    }
    /**
     * @return PropertyFetch[]
     */
    public function findLocalPropertyFetchesByName(\PhpParser\Node\Stmt\Class_ $class, string $paramName) : array
    {
        /** @var PropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->findInstanceOf($class, \PhpParser\Node\Expr\PropertyFetch::class);
        $foundPropertyFetches = [];
        foreach ($propertyFetches as $propertyFetch) {
            if (!$this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($propertyFetch->name, $paramName)) {
                continue;
            }
            $foundPropertyFetches[] = $propertyFetch;
        }
        return $foundPropertyFetches;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $propertyOrPromotedParam
     */
    private function resolvePropertyName($propertyOrPromotedParam) : ?string
    {
        if ($propertyOrPromotedParam instanceof \PhpParser\Node\Stmt\Property) {
            return $this->nodeNameResolver->getName($propertyOrPromotedParam->props[0]);
        }
        return $this->nodeNameResolver->getName($propertyOrPromotedParam->var);
    }
}
