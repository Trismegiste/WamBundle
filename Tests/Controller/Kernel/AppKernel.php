<?php

/*
 * WamBundle
 */

namespace Trismegiste\WamBundle\Tests\Controller\Kernel;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
        $config = array(
            'secret' => '$ecret',
            'router' => array(
                'resource' => '%kernel.root_dir%/routing_test.yml'
            ),
            'form' => NULL,
            'csrf_protection' => NULL,
            'validation' => array(
                'enable_annotations' => true
            ),
            'templating' => array(
                'engines' => array(0 => 'twig')
            ),
            'fragments' => NULL,
            'test' => NULL,
            'session' => array(
                'storage_id' => 'session.storage.mock_file'
            ),
            'profiler' => array(
                'collect' => false
            )
        );

        $loader->load(function(ContainerBuilder $container) use ($config) {
                    $container->loadFromExtension('framework', $config);
                });
    }

}