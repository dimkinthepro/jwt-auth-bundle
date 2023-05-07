<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Tests\Feature\Infrastructure\Provider;

use DimkinThePro\JwtAuth\Application\Component\Encoder\FieldsEncoderInterface;
use DimkinThePro\JwtAuth\Application\Component\Provider\CryptoConfigurationProvider;
use DimkinThePro\JwtAuth\Application\Component\Provider\KeyProviderInterface;
use DimkinThePro\JwtAuth\Application\Component\Provider\PayloadForSignProviderInterface;
use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;
use DimkinThePro\JwtAuth\Domain\Enum\AlgorithmEnum;
use DimkinThePro\JwtAuth\Domain\Enum\TokenTypeEnum;
use DimkinThePro\JwtAuth\Infrastructure\Encoder\Base64FieldsEncoder;
use DimkinThePro\JwtAuth\Infrastructure\Provider\JwtTokenSigner;
use DimkinThePro\JwtAuth\Infrastructure\Provider\KeyProvider;
use DimkinThePro\JwtAuth\Infrastructure\Service\KeyPairGeneratorService;
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
