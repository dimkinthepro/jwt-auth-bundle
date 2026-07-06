<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Domain\Enum;

enum KeyLengthEnum: int
{
    case l4096 = 4096;
    case l2048 = 2048;
    case l1024 = 1024;
    case l512 = 512;
    case l384 = 384;
}
