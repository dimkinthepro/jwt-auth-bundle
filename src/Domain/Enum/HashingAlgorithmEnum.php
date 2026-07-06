<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Domain\Enum;

enum HashingAlgorithmEnum: int
{
    private const CONST_SHA512 = \OPENSSL_ALGO_SHA512;
    private const CONST_SHA384 = \OPENSSL_ALGO_SHA384;
    private const CONST_SHA256 = \OPENSSL_ALGO_SHA256;

    case SHA512 = self::CONST_SHA512;
    case SHA384 = self::CONST_SHA384;
    case SHA256 = self::CONST_SHA256;
}
