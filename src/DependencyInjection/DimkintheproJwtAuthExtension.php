<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\DependencyInjection;

use Dimkinthepro\JwtAuth\Application\Component\Blocklist\TokenBlocklistInterface;
use Dimkinthepro\JwtAuth\Infrastructure\Blocklist\CacheTokenBlocklist;
use Dimkinthepro\JwtAuth\Infrastructure\Blocklist\NullTokenBlocklist;
use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\BearerTokenExtractor;
use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\ChainTokenExtractor;
use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\CookieTokenExtractor;
use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\QueryParameterTokenExtractor;
use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\SplitCookieTokenExtractor;
use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\TokenExtractorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DimkintheproJwtAuthExtension extends Extension
{
    // Chain priority: the most specific and standard source first
    private const TOKEN_EXTRACTOR_MAP = [
        Configuration::AUTHORIZATION_HEADER_KEY => BearerTokenExtractor::class,
        Configuration::SPLIT_COOKIE_KEY => SplitCookieTokenExtractor::class,
        Configuration::COOKIE_KEY => CookieTokenExtractor::class,
        Configuration::QUERY_PARAMETER_KEY => QueryParameterTokenExtractor::class,
    ];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $this->configureTokenExtractors($container, $config[Configuration::TOKEN_EXTRACTORS_KEY]);
        $this->configureBlocklist($container, $config[Configuration::BLOCKLIST_KEY]);

        foreach ($config as $key => $value) {
            $container->setParameter(\sprintf('%s.config.%s', Configuration::ROOT_NODE, $key), $value);
        }
    }

    private function configureTokenExtractors(ContainerBuilder $container, array $config): void
    {
        $container->getDefinition(CookieTokenExtractor::class)
            ->setArgument('$cookieName', $config[Configuration::COOKIE_KEY][Configuration::NAME_KEY]);

        $container->getDefinition(QueryParameterTokenExtractor::class)
            ->setArgument('$parameterName', $config[Configuration::QUERY_PARAMETER_KEY][Configuration::NAME_KEY]);

        $container->getDefinition(SplitCookieTokenExtractor::class)
            ->setArgument(
                '$payloadCookieName',
                $config[Configuration::SPLIT_COOKIE_KEY][Configuration::PAYLOAD_COOKIE_NAME_KEY]
            )
            ->setArgument(
                '$signatureCookieName',
                $config[Configuration::SPLIT_COOKIE_KEY][Configuration::SIGNATURE_COOKIE_NAME_KEY]
            );

        $enabledExtractors = [];
        foreach (self::TOKEN_EXTRACTOR_MAP as $configKey => $extractorClass) {
            if (true === $config[$configKey][Configuration::ENABLED_KEY]) {
                $enabledExtractors[] = new Reference($extractorClass);
            }
        }

        $container->getDefinition(ChainTokenExtractor::class)->setArgument('$extractors', $enabledExtractors);
        $container->setAlias(TokenExtractorInterface::class, ChainTokenExtractor::class);
    }

    private function configureBlocklist(ContainerBuilder $container, array $config): void
    {
        $container->setParameter(
            \sprintf('%s.config.blocklist_enabled', Configuration::ROOT_NODE),
            $config[Configuration::ENABLED_KEY]
        );

        if (true !== $config[Configuration::ENABLED_KEY]) {
            $container->removeDefinition(CacheTokenBlocklist::class);
            $container->setAlias(TokenBlocklistInterface::class, NullTokenBlocklist::class);

            return;
        }

        $container->getDefinition(CacheTokenBlocklist::class)
            ->setArgument('$cache', new Reference($config[Configuration::CACHE_POOL_KEY]));
        $container->setAlias(TokenBlocklistInterface::class, CacheTokenBlocklist::class);
    }
}
