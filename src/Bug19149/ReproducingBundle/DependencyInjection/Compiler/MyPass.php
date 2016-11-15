<?php

namespace Bug19149\ReproducingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class MyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $config = $container->getExtensionConfig('bug19149_reproducing');

        // output config
        print_r(PHP_EOL);print_r(__CLASS__ . ": ");print_r(PHP_EOL);
        print_r("  ");print_r(json_encode($config)); print_r(PHP_EOL);
    }
}