<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Domain\Enum;

enum AlgorithmEnum: string
{
    public const CONST_RS512 = 'RS512';
    public const CONST_RS384 = 'RS384';
    public const CONST_RS256 = 'RS256';

    case RS512 = self::CONST_RS512;
    case RS384 = self::CONST_RS384;
    case RS256 = self::CONST_RS256;
}
