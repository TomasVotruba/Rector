<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\Tests;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\Utils\NodeDocumentationGenerator\NodeInfosFactory;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class NodeInfosFactoryTest extends AbstractKernelTestCase
{
    /**
     * @var NodeInfosFactory
     */
    private $nodeInfosFactory;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->nodeInfosFactory = self::$container->get(NodeInfosFactory::class);
    }

    public function test(): void
    {
        $nodeInfos = $this->nodeInfosFactory->create();
        $this->assertGreaterThan(4, $nodeInfos->getCategories());
    }
}
