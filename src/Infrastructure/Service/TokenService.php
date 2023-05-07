<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Service;

use DimkinThePro\CommandQuery\Command\CommandBusInterface;
use DimkinThePro\CommandQuery\Query\QueryBusInterface;
use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;
use DimkinThePro\JwtAuth\Domain\Entity\RefreshToken;
use DimkinThePro\JwtAuth\Message\Command\CreateJwtTokenCommand;
use DimkinThePro\JwtAuth\Message\Command\CreateRefreshTokenCommand;
use DimkinThePro\JwtAuth\Message\Command\RefreshTokenCommand;
use DimkinThePro\JwtAuth\Message\Query\ExtractJwtTokenQuery;

class TokenService
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function createJwtToken(string $userIdentifier): JwtToken
    {
        $command = new CreateJwtTokenCommand($userIdentifier);

        return $this->commandBus->execute($command);
    }

    public function createRefreshToken(string $userIdentifier): RefreshToken
    {
        $command = new CreateRefreshTokenCommand($userIdentifier);

        return $this->commandBus->execute($command);
    }

    public function refreshRefreshToken(string $token): RefreshToken
    {
        $command = new RefreshTokenCommand($token);

        return $this->commandBus->execute($command);
    }

    public function extractJwtToken(string $token): JwtToken
    {
        $query = new ExtractJwtTokenQuery($token);

        return $this->queryBus->execute($query);
    }
}
