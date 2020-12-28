<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use PhpParser\Node;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

/**
 * This prevents importing:
 * - App\Some\Product
 *
 * if there is already:
 * - use App\Another\Product
 */
final class UsesClassNameImportSkipVoter implements ClassNameImportSkipVoterInterface
{
    /**
     * @var UseNodesToAddCollector
     */
    private $useNodesToAddCollector;

    public function __construct(UseNodesToAddCollector $useNodesToAddCollector)
    {
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }

    public function shouldSkip(FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool
    {
        $useImportTypes = $this->useNodesToAddCollector->getUseImportTypesByNode($node);

        foreach ($useImportTypes as $useImportType) {
            if (! $useImportType->equals($fullyQualifiedObjectType) && $useImportType->areShortNamesEqual(
                $fullyQualifiedObjectType
            )
            ) {
                return true;
            }

            if ($useImportType->equals($fullyQualifiedObjectType)) {
                return false;
            }
        }

        return false;
    }
}
