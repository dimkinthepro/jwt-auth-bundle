<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Domain\Enum;

enum KeyTypeEnum: int
{
    private const CONST_RSA = \OPENSSL_KEYTYPE_RSA;

    case RSA = self::CONST_RSA;
}
