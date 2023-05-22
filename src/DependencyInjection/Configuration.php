<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dimkinthepro_jwt_auth');

        $treeBuilder
            ->getRootNode()
            ->children()
                ->arrayNode('parameters')
                    ->children()
                        ->scalarNode('env(DIMKINTHEPRO_JWT_AUTH_PUBLIC_KEY)')
                            ->defaultValue('%kernel.project_dir%/var/dimkinthepro/jwt-auth-bundle/public.pem')
                        ->end()
                        ->scalarNode('env(DIMKINTHEPRO_JWT_AUTH_PRIVATE_KEY)')
                            ->defaultValue('%kernel.project_dir%/var/dimkinthepro/jwt-auth-bundle/private.pem')
                        ->end()
                        ->scalarNode('env(DIMKINTHEPRO_JWT_AUTH_PASSPHRASE)')
                            ->defaultValue(bin2hex(random_bytes(20)))
                        ->end()
                    ->end() // children
                ->end() // parameters

                ->arrayNode('framework')
                    ->children()
                        ->arrayNode('messenger')
                            ->children()
                                ->scalarNode('default_bus')
                                    ->defaultValue('query_bus')
                                ->end() // default_bus
                                ->arrayNode('buses')
                                    ->children()
                                        ->scalarNode('query_bus')
                                            ->defaultNull()
                                        ->end() // query_bus
                                        ->arrayNode('command_bus')
                                            ->children()
                                                ->arrayNode('middleware')
                                                ->end() // middleware
                                            ->end() // children
                                        ->end() // command_bus
                                    ->end() // children
                                ->end() // buses
                            ->end() // children
                        ->end() // messenger
                    ->end() // children
                ->end() // framework
            ->end()
        ;

        return $treeBuilder;
    }
}
