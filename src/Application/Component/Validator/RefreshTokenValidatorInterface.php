<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Validator;

use DimkinThePro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use DimkinThePro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenValidatorInterface
{
    /**
     * @throws JwtAuthExceptionInterface
     */
    public function validate(RefreshToken $refreshToken): void;
}
