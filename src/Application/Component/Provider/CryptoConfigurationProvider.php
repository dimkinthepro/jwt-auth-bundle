<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Provider;

use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\DigestAlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\HashingAlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\KeyLengthEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\KeyTypeEnum;

class CryptoConfigurationProvider
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

    public function getDigestAlgorithm(AlgorithmEnum $algorithm): DigestAlgorithmEnum
    {
        return self::DIGEST_ALGORITHM_MAP[$algorithm->value];
    }

    public function getHashingAlgorithm(AlgorithmEnum $algorithm): HashingAlgorithmEnum
    {
        return self::HASHING_ALGORITHM_MAP[$algorithm->value];
    }

    public function getKeyLength(AlgorithmEnum $algorithm): KeyLengthEnum
    {
        return self::KEY_LENGTH_MAP[$algorithm->value];
    }

    public function getKeyType(AlgorithmEnum $algorithm): KeyTypeEnum
    {
        return self::KEY_TYPE_MAP[$algorithm->value];
    }
}
