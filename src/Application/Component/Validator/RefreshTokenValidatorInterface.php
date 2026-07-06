<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Validator;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenValidatorInterface
{
    /**
     * @throws JwtAuthExceptionInterface
     */
    public function validate(RefreshToken $refreshToken): void;
}
