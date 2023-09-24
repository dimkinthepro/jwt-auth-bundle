<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const ROOT_NODE = 'dimkinthepro_jwt_auth';
    public const PASSPHRASE_KEY = 'passphrase';
    public const PUBLIC_KEY_PATH_KEY = 'public_key_path';
    public const PRIVATE_KEY_PATH_KEY = 'private_key_path';
    public const TOKEN_TTL_KEY = 'token_ttl';
    public const ALGORITHM_KEY = 'algorithm';
    public const REFRESH_TOKEN_TTL_KEY = 'refresh_token_ttl';
    public const REFRESH_TOKEN_LENGTH_KEY = 'refresh_token_length';
    public const PUBLIC_KEY_PATH_VALUE = '%kernel.project_dir%/var/dimkinthepro/jwt-auth-bundle/public.pem';
    public const PRIVATE_KEY_PATH_VALUE = '%kernel.project_dir%/var/dimkinthepro/jwt-auth-bundle/private.pem';
    public const TOKEN_TTL_VALUE = 36000;
    public const ALGORITHM_VALUE = 'RS512';
    public const REFRESH_TOKEN_TTL_VALUE = 2592000;
    public const REFRESH_TOKEN_LENGTH_VALUE = 128;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ROOT_NODE);

        $treeBuilder
            ->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode(self::PUBLIC_KEY_PATH_KEY)
                    ->defaultValue(self::PUBLIC_KEY_PATH_VALUE)
                ->end()
                ->scalarNode(self::PRIVATE_KEY_PATH_KEY)
                    ->defaultValue(self::PRIVATE_KEY_PATH_VALUE)
                ->end()
                ->scalarNode(self::PASSPHRASE_KEY)
                    ->defaultValue($this->getDefaultPassphrase())
                ->end()
                ->scalarNode(self::TOKEN_TTL_KEY)
                    ->defaultValue(self::TOKEN_TTL_VALUE)
                ->end()
                ->scalarNode(self::ALGORITHM_KEY)
                    ->defaultValue(self::ALGORITHM_VALUE)
                ->end()
                ->scalarNode(self::REFRESH_TOKEN_TTL_KEY)
                    ->defaultValue(self::REFRESH_TOKEN_TTL_VALUE)
                ->end()
                ->scalarNode(self::REFRESH_TOKEN_LENGTH_KEY)
                    ->defaultValue(self::REFRESH_TOKEN_LENGTH_VALUE)
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function getDefaultPassphrase(): string
    {
        return bin2hex(random_bytes(20));
    }
}
