<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Provider;

use DimkinThePro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;

interface KeyProviderInterface
{
    /**
     * @throws JwtAuthExceptionInterface
     */
    public function getPublicKey(): \OpenSSLAsymmetricKey;

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function getPrivateKey(): \OpenSSLAsymmetricKey;
}
