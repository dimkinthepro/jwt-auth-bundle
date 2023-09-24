<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Provider;

use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Provider\KeyProvider;
use Dimkinthepro\JwtAuth\Infrastructure\Service\KeyPairGeneratorService;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class KeyProviderTest extends TestCase
{
    /**
     * @dataProvider providerTestCase
     */
    public function testCase(
        array $keyPaths,
        string $passPhrase,
        string $exceptionClass = null,
        string $exceptionMessage = null
    ): void {
        if (null !== $exceptionClass && null !== $exceptionMessage) {
            /* @phpstan-ignore-next-line */
            self::expectException($exceptionClass);
            self::expectExceptionMessage($exceptionMessage);
        }

        $provider = new KeyProvider($keyPaths[0], $keyPaths[1], $passPhrase);

        self::assertInstanceOf(\OpenSSLAsymmetricKey::class, $provider->getPublicKey());
        self::assertInstanceOf(\OpenSSLAsymmetricKey::class, $provider->getPrivateKey());

        foreach ($keyPaths as $keyPath) {
            if (true === is_file($keyPath)) {
                unlink($keyPath);
            }
        }
    }

    public function providerTestCase(): array
    {
        $result = [
            'failKeyPath' => [
                'keyPath' => $this->createKeyFiles(),
                'passPhrase' => '',
                'exceptionClass' => \RuntimeException::class,
                'exceptionMessage' => '176acbbe-cd2e-4d0a-8399-48c81dd2a5e3',
            ],
        ];

        foreach (AlgorithmEnum::cases() as $algorithm) {
            $passPhrase = Factory::create()->password();
            $result[] = [
                'keyPath' => $this->createKeyFiles($algorithm, $passPhrase),
                'passPhrase' => $passPhrase,
                'exceptionClass' => null,
                'exceptionMessage' => null,
            ];
        }

        return $result;
    }

    private function createKeyFiles(
        AlgorithmEnum $algorithm = null,
        string $passphrase = null
    ): array {
        if (null === $algorithm && null === $passphrase) {
            return [
                'publicPath',
                'privatePath',
            ];
        }

        $publicPath = __DIR__ . '/public.pem' . $algorithm->value;
        $privatePath = __DIR__ . '/private.pem' . $algorithm->value;
        $service = new KeyPairGeneratorService();
        $keyPair = $service->generate($algorithm, $passphrase);
        file_put_contents($publicPath, $keyPair->publicKey);
        file_put_contents($privatePath, $keyPair->privateKey);

        return [
            $publicPath,
            $privatePath,
        ];
    }
}
