<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Provider;

use Dimkinthepro\JwtAuth\Application\Component\Encoder\FieldsEncoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\CryptoConfigurationProvider;
use Dimkinthepro\JwtAuth\Application\Component\Provider\JwtTokenSignerInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\KeyProviderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\PayloadForSignProviderInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;

class JwtTokenSigner implements JwtTokenSignerInterface
{
    public function __construct(
        private readonly KeyProviderInterface $keyProvider,
        private readonly PayloadForSignProviderInterface $payloadForSignProvider,
        private readonly CryptoConfigurationProvider $cryptoConfigurationProvider,
        private readonly FieldsEncoderInterface $fieldsEncoder,
    ) {
    }

    public function sign(JwtToken $jwtToken): void
    {
        $payload = $this->payloadForSignProvider->getPayload($jwtToken);
        $algorithm = $this->cryptoConfigurationProvider->getHashingAlgorithm($jwtToken->getAlgorithm());
        $privateKey = $this->keyProvider->getPrivateKey();

        openssl_sign($payload, $signature, $privateKey, $algorithm->value);

        $signature = $this->fieldsEncoder->encode($signature);
        $jwtToken->setSignature($signature);
    }
}
