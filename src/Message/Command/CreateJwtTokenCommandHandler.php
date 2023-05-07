<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Message\Command;

use DimkinThePro\CommandQuery\Command\CommandHandlerInterface;
use DimkinThePro\JwtAuth\Application\UseCase\JwtToken\JwtTokenCreator;
use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;

class CreateJwtTokenCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly JwtTokenCreator $creator
    ) {
    }

    public function __invoke(CreateJwtTokenCommand $command): JwtToken
    {
        return $this->creator->create($command->userIdentifier);
    }
}
