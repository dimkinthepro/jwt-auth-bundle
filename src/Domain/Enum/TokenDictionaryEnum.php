<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Domain\Enum;

enum TokenDictionaryEnum: string
{
    case ALGORITHM = 'alg';
    case TYPE = 'typ';
    case ISSUED_AT = 'iat';
    case EXPIRED_AT = 'exp';
    case IDENTIFIER = 'identifier';
}
