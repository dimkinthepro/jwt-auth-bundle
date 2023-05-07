<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Validator;

use DimkinThePro\JwtAuth\Application\Component\Validator\RefreshTokenValidatorInterface;
use DimkinThePro\JwtAuth\Domain\Entity\RefreshToken;
use DimkinThePro\JwtAuth\Infrastructure\Exception\RefreshTokenExpiredException;
use DimkinThePro\JwtAuth\Infrastructure\Factory\DateTimeFactory;

class RefreshTokenValidator implements RefreshTokenValidatorInterface
{
    public function __construct(
        private readonly DateTimeFactory $dateTimeFactory,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function validate(RefreshToken $refreshToken): void
    {
        if ($this->dateTimeFactory->getNowDate() > $refreshToken->getValidUntil()) {
            throw new RefreshTokenExpiredException('2d61750a-97f4-4911-8981-84a5153d9550 Refresh token expired');
        }
    }
}
