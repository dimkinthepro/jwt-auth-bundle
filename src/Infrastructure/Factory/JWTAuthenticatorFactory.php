<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class JWTAuthenticatorFactory implements AuthenticatorFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return -10;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return 'auth_jwt';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $builder): void
    {
        /* @phpstan-ignore-next-line */
        $builder->children()
                ->scalarNode('provider')
                    ->defaultNull()
                ->end()
                ->scalarNode('auth_jwt_authenticator')
                    ->defaultValue('DimkinThePro\JwtAuth\Infrastructure\Security\JWTAuthenticator')
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function createAuthenticator(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        string $userProviderId
    ): string|array {
        $authenticatorId = 'security.authenticator.auth_jwt.' . $firewallName;
        $container->setDefinition($authenticatorId, new ChildDefinition($config['auth_jwt_authenticator']));

        return $authenticatorId;
    }
}
