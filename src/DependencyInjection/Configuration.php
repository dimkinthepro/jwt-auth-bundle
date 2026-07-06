<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

readonly class Configuration implements ConfigurationInterface
{
    public const ROOT_NODE = 'dimkinthepro_jwt_auth';
    public const PASSPHRASE_KEY = 'passphrase';
    public const PUBLIC_KEY_PATH_KEY = 'public_key_path';
    public const PRIVATE_KEY_PATH_KEY = 'private_key_path';
    public const TOKEN_TTL_KEY = 'token_ttl';
    public const ALGORITHM_KEY = 'algorithm';
    public const REFRESH_TOKEN_TTL_KEY = 'refresh_token_ttl';
    public const REFRESH_TOKEN_LENGTH_KEY = 'refresh_token_length';
    public const ISSUER_KEY = 'issuer';
    public const AUDIENCE_KEY = 'audience';
    public const CLOCK_SKEW_LEEWAY_KEY = 'clock_skew_leeway';
    public const TOKEN_EXTRACTORS_KEY = 'token_extractors';
    public const AUTHORIZATION_HEADER_KEY = 'authorization_header';
    public const COOKIE_KEY = 'cookie';
    public const QUERY_PARAMETER_KEY = 'query_parameter';
    public const SPLIT_COOKIE_KEY = 'split_cookie';
    public const ENABLED_KEY = 'enabled';
    public const NAME_KEY = 'name';
    public const PAYLOAD_COOKIE_NAME_KEY = 'payload_cookie_name';
    public const SIGNATURE_COOKIE_NAME_KEY = 'signature_cookie_name';
    public const COOKIE_NAME_VALUE = 'jwt_token';
    public const QUERY_PARAMETER_NAME_VALUE = 'jwt_token';
    public const PAYLOAD_COOKIE_NAME_VALUE = 'jwt_hp';
    public const SIGNATURE_COOKIE_NAME_VALUE = 'jwt_sig';
    public const BLOCKLIST_KEY = 'blocklist';
    public const CACHE_POOL_KEY = 'cache_pool';
    public const CACHE_POOL_VALUE = 'cache.app';
    public const PUBLIC_KEY_PATH_VALUE = '%kernel.project_dir%/var/dimkinthepro/jwt-auth-bundle/public.pem';
    public const PRIVATE_KEY_PATH_VALUE = '%kernel.project_dir%/var/dimkinthepro/jwt-auth-bundle/private.pem';
    // Access tokens should be short-lived: expired ones are renewed via the refresh token flow
    public const TOKEN_TTL_VALUE = 900;
    public const ALGORITHM_VALUE = 'RS512';
    public const REFRESH_TOKEN_TTL_VALUE = 2592000;
    public const REFRESH_TOKEN_LENGTH_VALUE = 128;
    // Tolerated clock difference (seconds) between the issuing and validating servers
    public const CLOCK_SKEW_LEEWAY_VALUE = 60;

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
                    ->info(
                        'Passphrase for the private key. Must be set explicitly: a generated default would '
                        . 'change on every container rebuild and invalidate all issued tokens.'
                    )
                    ->isRequired()
                    ->cannotBeEmpty()
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
                ->scalarNode(self::ISSUER_KEY)
                    ->info('Value of the "iss" claim; the claim is omitted and not validated when null.')
                    ->defaultNull()
                ->end()
                ->scalarNode(self::AUDIENCE_KEY)
                    ->info('Value of the "aud" claim; the claim is omitted and not validated when null.')
                    ->defaultNull()
                ->end()
                ->scalarNode(self::CLOCK_SKEW_LEEWAY_KEY)
                    ->info('Tolerated clock skew in seconds when validating "exp", "nbf" and "iat" claims.')
                    ->defaultValue(self::CLOCK_SKEW_LEEWAY_VALUE)
                ->end()
                ->arrayNode(self::TOKEN_EXTRACTORS_KEY)
                    ->info('Enabled extractors are chained.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode(self::AUTHORIZATION_HEADER_KEY)
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode(self::ENABLED_KEY)->defaultTrue()->end()
                            ->end()
                        ->end()
                        ->arrayNode(self::COOKIE_KEY)
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode(self::ENABLED_KEY)->defaultFalse()->end()
                                ->scalarNode(self::NAME_KEY)
                                    ->cannotBeEmpty()
                                    ->defaultValue(self::COOKIE_NAME_VALUE)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(self::QUERY_PARAMETER_KEY)
                            ->info('For transports without headers (WebSocket, SSE); tokens in URLs leak into access logs.')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode(self::ENABLED_KEY)->defaultFalse()->end()
                                ->scalarNode(self::NAME_KEY)
                                    ->cannotBeEmpty()
                                    ->defaultValue(self::QUERY_PARAMETER_NAME_VALUE)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(self::SPLIT_COOKIE_KEY)
                            ->info('Token split into two cookies: "header.payload" (JS-readable) and signature (HttpOnly).')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode(self::ENABLED_KEY)->defaultFalse()->end()
                                ->scalarNode(self::PAYLOAD_COOKIE_NAME_KEY)
                                    ->cannotBeEmpty()
                                    ->defaultValue(self::PAYLOAD_COOKIE_NAME_VALUE)
                                ->end()
                                ->scalarNode(self::SIGNATURE_COOKIE_NAME_KEY)
                                    ->cannotBeEmpty()
                                    ->defaultValue(self::SIGNATURE_COOKIE_NAME_VALUE)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode(self::BLOCKLIST_KEY)
                    ->info('Instant access token revocation by the "sid" claim; costs one cache lookup per authenticated request.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode(self::ENABLED_KEY)->defaultFalse()->end()
                        ->scalarNode(self::CACHE_POOL_KEY)
                            ->info('PSR-6 cache pool service id used as the blocklist storage.')
                            ->cannotBeEmpty()
                            ->defaultValue(self::CACHE_POOL_VALUE)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
