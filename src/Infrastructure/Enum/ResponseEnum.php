<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Enum;

enum ResponseEnum: string
{
    case DATA = 'data';
    case ERRORS = 'errors';
    case MESSAGE = 'message';
}
