<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Message\Command;

use DimkinThePro\CommandQuery\Command\CommandHandlerInterface;
use DimkinThePro\JwtAuth\Application\UseCase\RefreshToken\RefreshTokenCreator;
use DimkinThePro\JwtAuth\Domain\Entity\RefreshToken;

class CreateRefreshTokenCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly RefreshTokenCreator $creator
    ) {
    }

    public function __invoke(CreateRefreshTokenCommand $command): RefreshToken
    {
        return $this->creator->create($command->userIdentifier);
    }
}
