<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Service;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\CryptoConfigurationProvider;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Infrastructure\DTO\KeyPairDto;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\OpenSslException;

class KeyPairGeneratorService
{
    /**
     * @throws JwtAuthExceptionInterface
     */
    public function generate(AlgorithmEnum $algorithm, string $passphrase): KeyPairDto
    {
        $config = $this->getOpenSSLConfiguration($algorithm);

        $resource = openssl_pkey_new($config);
        if (false === $resource) {
            throw new OpenSslException('06fb1f18-b20f-4ca7-bdb2-515d7aff0c7a ' . openssl_error_string());
        }

        $success = openssl_pkey_export($resource, $privateKey, $passphrase);

        if (false === $success) {
            throw new OpenSslException('91c9f52a-316e-4d1c-bf4e-14ebeebc2fb6 ' . openssl_error_string());
        }

        $publicKeyData = openssl_pkey_get_details($resource);

        if (false === $publicKeyData) {
            throw new OpenSslException('ab83243e-c770-4920-8604-b8efb33f8d9c ' . openssl_error_string());
        }

        $publicKey = $publicKeyData['key'];

        return new KeyPairDto($publicKey, $privateKey);
    }

    private function getOpenSSLConfiguration(AlgorithmEnum $algorithm): array
    {
        $configurationProvider = new CryptoConfigurationProvider();
        $digestAlgorithm = $configurationProvider->getDigestAlgorithm($algorithm);
        $keyLength = $configurationProvider->getKeyLength($algorithm);
        $keyType = $configurationProvider->getKeyType($algorithm);

        return [
            'digest_alg' => $digestAlgorithm->value,
            'private_key_bits' => $keyLength->value,
            'private_key_type' => $keyType->value,
        ];
    }
}
