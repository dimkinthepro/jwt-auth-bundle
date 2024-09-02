<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Provider;

use Dimkinthepro\JwtAuth\Application\Component\Provider\KeyProviderInterface;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\ExtractPrivateKeyException;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\ExtractPublicKeyException;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\FileNotFoundException;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\ReadFileException;

class KeyProvider implements KeyProviderInterface
{
    public function __construct(
        private readonly string $authPublicKey,
        private readonly string $authPrivateKey,
        private readonly string $authPassphrase,
    ) {
    }

    public function getPublicKey(): \OpenSSLAsymmetricKey
    {
        $key = $this->readKey($this->authPublicKey);
        $resource = openssl_pkey_get_public($key);
        if (false === $resource) {
            throw new ExtractPublicKeyException(sprintf(
                'f41111b9-f16c-403d-a4b6-a18ddb61610b Public key read error by path: "%s"',
                $this->authPublicKey
            ));
        }

        return $resource;
    }

    public function getPrivateKey(): \OpenSSLAsymmetricKey
    {
        $key = $this->readKey($this->authPrivateKey);
        $resource = openssl_pkey_get_private($key, $this->authPassphrase);
        if (false === $resource) {
            throw new ExtractPrivateKeyException(sprintf(
                '9c3e9916-63f8-4f7f-9de2-bc01fa4a2d3a Private key read error by path: "%s"',
                $this->authPrivateKey
            ));
        }

        return $resource;
    }

    /**
     * @throws FileNotFoundException|ReadFileException
     */
    private function readKey(string $path): string
    {
        if (false === is_file($path) || false === is_readable($path)) {
            throw new FileNotFoundException(sprintf(
                '176acbbe-cd2e-4d0a-8399-48c81dd2a5e3 Key file not found by path: "%s"',
                $path
            ));
        }

        $content = file_get_contents($path);
        if (false === $content) {
            throw new ReadFileException(sprintf(
                'ab67618e-05e9-4209-b8ec-90d12d727754 Read file error by path: "%s"',
                $path
            ));
        }

        return $content;
    }
}
