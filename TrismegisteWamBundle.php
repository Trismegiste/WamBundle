<?php

namespace Trismegiste\WamBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TrismegisteWamBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        $container->register('prolog.wam', 'Trismegiste\Prolog\WAMService');
    }

}
