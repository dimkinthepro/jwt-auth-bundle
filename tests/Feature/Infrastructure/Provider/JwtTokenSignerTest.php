<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Infrastructure\Provider;

use Dimkinthepro\JwtAuth\Application\Component\Encoder\FieldsEncoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\CryptoConfigurationProvider;
use Dimkinthepro\JwtAuth\Application\Component\Provider\KeyProviderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\PayloadForSignProviderInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Encoder\Base64FieldsEncoder;
use Dimkinthepro\JwtAuth\Infrastructure\Provider\JwtTokenSigner;
use Dimkinthepro\JwtAuth\Infrastructure\Provider\KeyProvider;
use Dimkinthepro\JwtAuth\Infrastructure\Service\KeyPairGeneratorService;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class JwtTokenSignerTest extends KernelTestCase
{
    private const PUBLIC_FILE_PATH = __DIR__ . '/public.pem';
    private const PRIVATE_FILE_PATH = __DIR__ . '/private.pem';
    private const FILES = [
        self::PUBLIC_FILE_PATH,
        self::PRIVATE_FILE_PATH,
    ];

    private const PASSPHRASE = '';
    private const PAYLOAD = 'some phrase';

    protected function tearDown(): void
    {
        foreach (self::FILES as $file) {
            if (true === is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * @dataProvider testSignProvider
     */
    public function testSign(AlgorithmEnum $algorithm): void
    {
        $signService = $this->getSignService($algorithm);
        $token = new JwtToken(
            $algorithm,
            TokenTypeEnum::JWT,
            Factory::create()->email(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
        );

        $signService->sign($token);

        self::assertIsString($token->getSignature());
    }

    public function testSignProvider(): array
    {
        $result = [];
        foreach (AlgorithmEnum::cases() as $algorithm) {
            $result[] = [
                'algorithm' => $algorithm,
            ];
        }

        return $result;
    }

    private function getSignService(AlgorithmEnum $algorithm): JwtTokenSigner
    {
        return new JwtTokenSigner(
            $this->getKeyProvider($algorithm),
            $this->getJwtPayloadForSignService(),
            new CryptoConfigurationProvider(),
            $this->getFieldsEncoder(),
        );
    }

    private function getKeyProvider(AlgorithmEnum $algorithm): KeyProviderInterface
    {
        $generator = new KeyPairGeneratorService();
        $keyPair = $generator->generate($algorithm, self::PASSPHRASE);

        file_put_contents(self::PUBLIC_FILE_PATH, $keyPair->publicKey);
        file_put_contents(self::PRIVATE_FILE_PATH, $keyPair->privateKey);

        return new KeyProvider(
            self::PUBLIC_FILE_PATH,
            self::PRIVATE_FILE_PATH,
            self::PASSPHRASE
        );
    }

    private function getJwtPayloadForSignService(): PayloadForSignProviderInterface
    {
        $service = $this->createMock(PayloadForSignProviderInterface::class);
        $service->method('getPayload')->willReturn(self::PAYLOAD);

        return $service;
    }

    private function getFieldsEncoder(): FieldsEncoderInterface
    {
        return new Base64FieldsEncoder();
    }
}
