<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Message\Command;

use DimkinThePro\CommandQuery\Command\CommandInterface;

class CreateRefreshTokenCommand implements CommandInterface
{
    public function __construct(
        public readonly string $userIdentifier
    ) {
    }
}
