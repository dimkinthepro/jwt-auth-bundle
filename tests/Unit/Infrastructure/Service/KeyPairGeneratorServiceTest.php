<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Service;

use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Infrastructure\DTO\KeyPairDto;
use Dimkinthepro\JwtAuth\Infrastructure\Service\KeyPairGeneratorService;
use PHPUnit\Framework\TestCase;

class KeyPairGeneratorServiceTest extends TestCase
{
    /**
     * @dataProvider providerTestGenerate
     */
    public function testGenerate(
        AlgorithmEnum $algorithm,
        string $passphrase
    ): void {
        $service = new KeyPairGeneratorService();

        $keyPair = $service->generate($algorithm, $passphrase);

        self::assertInstanceOf(KeyPairDto::class, $keyPair);
        self::assertIsString($keyPair->publicKey);
        self::assertIsString($keyPair->privateKey);
    }

    public function providerTestGenerate(): array
    {
        $result = [];
        foreach (AlgorithmEnum::cases() as $algorithm) {
            $result[] = [
                'algorithm' => $algorithm,
                'passphrase' => 'passphrase',
            ];
        }

        return $result;
    }
}
