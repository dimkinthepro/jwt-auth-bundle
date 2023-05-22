<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth;

use DimkinThePro\JwtAuth\Infrastructure\Factory\JWTAuthenticatorFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JwtAuthBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addAuthenticatorFactory(new JWTAuthenticatorFactory());
    }
}
