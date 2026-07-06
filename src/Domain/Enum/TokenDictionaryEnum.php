<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Domain\Enum;

enum TokenDictionaryEnum: string
{
    case ALGORITHM = 'alg';
    case TYPE = 'typ';
    case ISSUED_AT = 'iat';
    case EXPIRED_AT = 'exp';
    case IDENTIFIER = 'identifier';
    case ISSUER = 'iss';
    case AUDIENCE = 'aud';
    case TOKEN_ID = 'jti';
    case NOT_BEFORE = 'nbf';
    case SESSION_ID = 'sid';
}
