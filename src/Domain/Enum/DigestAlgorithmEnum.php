<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Domain\Enum;

enum DigestAlgorithmEnum: string
{
    case sha256 = 'sha256';
    case sha384 = 'sha384';
    case sha512 = 'sha512';
}
