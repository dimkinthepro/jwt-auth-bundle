<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Message\Query;

use DimkinThePro\CommandQuery\Query\QueryInterface;

class ExtractJwtTokenQuery implements QueryInterface
{
    public function __construct(
        public readonly string $token
    ) {
    }
}
