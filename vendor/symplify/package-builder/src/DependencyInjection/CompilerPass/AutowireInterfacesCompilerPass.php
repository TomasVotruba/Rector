<?php

declare (strict_types=1);
namespace RectorPrefix20210720\Symplify\PackageBuilder\DependencyInjection\CompilerPass;

use RectorPrefix20210720\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix20210720\Symfony\Component\DependencyInjection\ContainerBuilder;
final class AutowireInterfacesCompilerPass implements \RectorPrefix20210720\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * @var mixed[]
     */
    private $typesToAutowire;
    /**
     * @param string[] $typesToAutowire
     */
    public function __construct(array $typesToAutowire)
    {
        $this->typesToAutowire = $typesToAutowire;
    }
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
     */
    public function process($containerBuilder) : void
    {
        $containerBuilderDefinitions = $containerBuilder->getDefinitions();
        foreach ($containerBuilderDefinitions as $definition) {
            foreach ($this->typesToAutowire as $typeToAutowire) {
                if (!\is_a((string) $definition->getClass(), $typeToAutowire, \true)) {
                    continue;
                }
                $definition->setAutowired(\true);
                continue 2;
            }
        }
    }
}
