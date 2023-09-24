<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Factory;

use Dimkinthepro\JwtAuth\Application\Component\Factory\RefreshTokenFactoryInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\RefreshTokenGeneratorInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

class RefreshTokenFactory implements RefreshTokenFactoryInterface
{
    public function __construct(
        private readonly int $authRefreshTokenTtl,
        private readonly RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private readonly DateTimeFactory $dateTimeFactory,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function create(string $userIdentifier): RefreshToken
    {
        $tokenValue = $this->refreshTokenGenerator->generate();
        $validDate = $this->dateTimeFactory->getNowDate(time() + $this->authRefreshTokenTtl);

        return new RefreshToken(
            $tokenValue,
            $userIdentifier,
            $validDate
        );
    }
}
