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
        print_r("  ");print_r(json_encode($config)); print_r(PHP_EOL); print_r(PHP_EOL);

        print_r('Solution to this:'); print_r(PHP_EOL);
        print_r("  ");print_r('set config into container in extension and then refer it in compiler pass like so '); print_r(PHP_EOL);
        print_r("  ");print_r('// in your XxxxxExtension.php '); print_r(PHP_EOL);
        print_r("  ");print_r('$container->setParameter("config", $config);'); print_r(PHP_EOL); print_r(PHP_EOL);
        print_r("  ");print_r('// in your XxxxxCompilerPass.php '); print_r(PHP_EOL);
        print_r("  ");print_r('$container->getParameter("config");'); print_r(PHP_EOL); print_r(PHP_EOL);
        print_r("  ");print_r('// then you will have an identical processed config like so '); print_r(PHP_EOL);
        $config1 = $container->getParameter("config");
        print_r("  ");print_r(json_encode($config1)); print_r(PHP_EOL);
    }
}