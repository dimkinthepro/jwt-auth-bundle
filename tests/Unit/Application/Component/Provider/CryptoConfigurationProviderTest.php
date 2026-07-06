<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Application\Component\Provider;

use Dimkinthepro\JwtAuth\Application\Component\Provider\CryptoConfigurationProvider;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\DigestAlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\HashingAlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\KeyLengthEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\KeyTypeEnum;
use PHPUnit\Framework\TestCase;

class CryptoConfigurationProviderTest extends TestCase
{
    private const DIGEST_ALGORITHM_MAP = [
        AlgorithmEnum::CONST_RS512 => DigestAlgorithmEnum::sha512,
        AlgorithmEnum::CONST_RS384 => DigestAlgorithmEnum::sha384,
        AlgorithmEnum::CONST_RS256 => DigestAlgorithmEnum::sha256,
    ];

    private const HASHING_ALGORITHM_MAP = [
        AlgorithmEnum::CONST_RS512 => HashingAlgorithmEnum::SHA512,
        AlgorithmEnum::CONST_RS384 => HashingAlgorithmEnum::SHA384,
        AlgorithmEnum::CONST_RS256 => HashingAlgorithmEnum::SHA256,
    ];

    private const KEY_LENGTH_MAP = [
        AlgorithmEnum::CONST_RS512 => KeyLengthEnum::l4096,
        AlgorithmEnum::CONST_RS384 => KeyLengthEnum::l2048,
        AlgorithmEnum::CONST_RS256 => KeyLengthEnum::l2048,
    ];

    private const KEY_TYPE_MAP = [
        AlgorithmEnum::CONST_RS512 => KeyTypeEnum::RSA,
        AlgorithmEnum::CONST_RS384 => KeyTypeEnum::RSA,
        AlgorithmEnum::CONST_RS256 => KeyTypeEnum::RSA,
    ];

    /**
     * @dataProvider algorithmProvider
     */
    public function testGetDigestAlgorithm(
        AlgorithmEnum $algorithm
    ): void {
        $provider = new CryptoConfigurationProvider();
        $result = $provider->getDigestAlgorithm($algorithm);

        self::assertInstanceOf(DigestAlgorithmEnum::class, $result);
        self::assertEquals(self::DIGEST_ALGORITHM_MAP[$algorithm->value], $result);
    }

    /**
     * @dataProvider algorithmProvider
     */
    public function testGetHashingAlgorithm(
        AlgorithmEnum $algorithm
    ): void {
        $provider = new CryptoConfigurationProvider();
        $result = $provider->getHashingAlgorithm($algorithm);

        self::assertInstanceOf(HashingAlgorithmEnum::class, $result);
        self::assertEquals(self::HASHING_ALGORITHM_MAP[$algorithm->value], $result);
    }

    /**
     * @dataProvider algorithmProvider
     */
    public function testGetKeyLength(
        AlgorithmEnum $algorithm
    ): void {
        $provider = new CryptoConfigurationProvider();
        $result = $provider->getKeyLength($algorithm);

        self::assertInstanceOf(KeyLengthEnum::class, $result);
        self::assertEquals(self::KEY_LENGTH_MAP[$algorithm->value], $result);
    }

    /**
     * @dataProvider algorithmProvider
     */
    public function testGetKeyType(
        AlgorithmEnum $algorithm
    ): void {
        $provider = new CryptoConfigurationProvider();
        $result = $provider->getKeyType($algorithm);

        self::assertInstanceOf(KeyTypeEnum::class, $result);
        self::assertEquals(self::KEY_TYPE_MAP[$algorithm->value], $result);
    }

    public function algorithmProvider(): array
    {
        $result = [];
        foreach (AlgorithmEnum::cases() as $case) {
            $result[] = [
                'algorithm' => $case,
            ];
        }

        return $result;
    }
}
