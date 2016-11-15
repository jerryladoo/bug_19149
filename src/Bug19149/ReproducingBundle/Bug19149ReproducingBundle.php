<?php

namespace Bug19149\ReproducingBundle;

use Bug19149\ReproducingBundle\DependencyInjection\Compiler\MyPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Bug19149ReproducingBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MyPass());
    }
}
