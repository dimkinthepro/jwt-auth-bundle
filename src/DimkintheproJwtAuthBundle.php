<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth;

use Dimkinthepro\JwtAuth\Infrastructure\Factory\JWTAuthenticatorFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DimkintheproJwtAuthBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addAuthenticatorFactory(new JWTAuthenticatorFactory());
    }
}
