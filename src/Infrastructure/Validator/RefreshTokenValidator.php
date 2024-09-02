<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Validator;

use Dimkinthepro\JwtAuth\Application\Component\Validator\RefreshTokenValidatorInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\RefreshTokenExpiredException;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;

class RefreshTokenValidator implements RefreshTokenValidatorInterface
{
    public function __construct(
        private readonly DateTimeFactory $dateTimeFactory,
    ) {
    }

    public function validate(RefreshToken $refreshToken): void
    {
        if ($this->dateTimeFactory->getNowDate() > $refreshToken->getValidUntil()) {
            throw new RefreshTokenExpiredException('2d61750a-97f4-4911-8981-84a5153d9550 Refresh token expired');
        }
    }
}
