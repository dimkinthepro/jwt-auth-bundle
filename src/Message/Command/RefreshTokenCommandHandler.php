<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Message\Command;

use DimkinThePro\CommandQuery\Command\CommandHandlerInterface;
use DimkinThePro\JwtAuth\Application\UseCase\RefreshToken\RefreshTokenRefresher;
use DimkinThePro\JwtAuth\Domain\Entity\RefreshToken;
use DimkinThePro\JwtAuth\Infrastructure\Exception\InvalidTokenException;

class RefreshTokenCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly RefreshTokenRefresher $refresher
    ) {
    }

    /**
     * @throws InvalidTokenException
     */
    public function __invoke(RefreshTokenCommand $command): RefreshToken
    {
        return $this->refresher->refresh($command->token);
    }
}
