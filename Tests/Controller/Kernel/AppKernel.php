<?php

/*
 * WamBundle
 */

namespace Trismegiste\WamBundle\Tests\Controller\Kernel;

use Symfony\Component\HttpKernel\Kernel;

/**
 * AppTestCase is the kernel for test this bundles
 */
class AppKernel extends Kernel
{

    public function registerBundles()
    {
        $bundles = array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Trismegiste\WamBundle\TrismegisteWamBundle()
        );

        return $bundles;
    }

    public function registerContainerConfiguration(\Symfony\Component\Config\Loader\LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config.yml');
    }

}