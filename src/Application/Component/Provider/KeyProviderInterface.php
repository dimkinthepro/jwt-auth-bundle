<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Provider;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;

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
