<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Domain\Enum;

enum TokenTypeEnum: string
{
    // RFC 7519, section 5.1: the "JWT" media type value is recommended to be spelled uppercase
    case JWT = 'JWT';
}
