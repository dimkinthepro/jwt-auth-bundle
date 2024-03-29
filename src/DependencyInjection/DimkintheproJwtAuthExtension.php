<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DimkintheproJwtAuthExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        foreach ($config as $key => $value) {
            $container->setParameter(sprintf('%s.config.%s', Configuration::ROOT_NODE, $key), $value);
        }
    }
}
