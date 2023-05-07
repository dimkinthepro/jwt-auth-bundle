<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Enum;

enum TokenResponseEnum: string
{
    case TOKEN = 'token';
    case REFRESH_TOKEN = 'refreshToken';
}
