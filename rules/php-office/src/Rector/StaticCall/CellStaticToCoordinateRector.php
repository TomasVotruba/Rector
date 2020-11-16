<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;

/**
 * @see https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#dedicated-class-to-manipulate-coordinates
 *
 * @see \Rector\PHPOffice\Tests\Rector\StaticCall\CellStaticToCoordinateRector\CellStaticToCoordinateRectorTest
 */
final class CellStaticToCoordinateRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const DECOUPLED_METHODS = [
        'absoluteCoordinate',
        'absoluteReference',
        'buildRange',
        'columnIndexFromString',
        'coordinateFromString',
        'extractAllCellReferencesInRange',
        'getRangeBoundaries',
        'mergeRangesInCollection',
        'rangeBoundaries',
        'rangeDimension',
        'splitRange',
        'stringFromColumnIndex',
    ];

    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition(
            'Methods to manipulate coordinates that used to exists in PHPExcel_Cell to PhpOffice\PhpSpreadsheet\Cell\Coordinate',
            [
                new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        \PHPExcel_Cell::stringFromColumnIndex();
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex();
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node->class, 'PHPExcel_Cell')) {
            return null;
        }

        if (! $this->isNames($node->name, self::DECOUPLED_METHODS)) {
            return null;
        }

        $node->class = new FullyQualified('PhpOffice\PhpSpreadsheet\Cell\Coordinate');

        return $node;
    }
}
