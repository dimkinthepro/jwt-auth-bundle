<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Provider;

use Dimkinthepro\JwtAuth\Application\Component\Encoder\FieldsEncoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\CryptoConfigurationProvider;
use Dimkinthepro\JwtAuth\Application\Component\Provider\JwtTokenSignerInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\KeyProviderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\PayloadForSignProviderInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\OpenSslException;

readonly class JwtTokenSigner implements JwtTokenSignerInterface
{
    public function __construct(
        private KeyProviderInterface $keyProvider,
        private PayloadForSignProviderInterface $payloadForSignProvider,
        private CryptoConfigurationProvider $cryptoConfigurationProvider,
        private FieldsEncoderInterface $fieldsEncoder,
    ) {
    }

    public function sign(JwtToken $jwtToken): void
    {
        $payload = $this->payloadForSignProvider->getPayload($jwtToken);
        $algorithm = $this->cryptoConfigurationProvider->getHashingAlgorithm($jwtToken->getAlgorithm());
        $privateKey = $this->keyProvider->getPrivateKey();

        $isSigned = openssl_sign($payload, $signature, $privateKey, $algorithm->value);
        if (false === $isSigned) {
            throw new OpenSslException(\sprintf(
                '5b1e174f-8f0e-4b39-9a0c-3f2d1c07f6d4 Token sign error: "%s"',
                (string) openssl_error_string()
            ));
        }

        $signature = $this->fieldsEncoder->encode($signature);
        $jwtToken->setSignature($signature);
    }
}
