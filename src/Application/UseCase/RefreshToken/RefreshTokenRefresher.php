<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\UseCase\RefreshToken;

use DimkinThePro\JwtAuth\Application\Component\Decoder\RefreshTokenDecoderInterface;
use DimkinThePro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use DimkinThePro\JwtAuth\Application\Component\Manager\RefreshTokenManager;
use DimkinThePro\JwtAuth\Application\Component\Validator\RefreshTokenValidatorInterface;
use DimkinThePro\JwtAuth\Domain\Entity\RefreshToken;

class RefreshTokenRefresher
{
    public function __construct(
        private readonly RefreshTokenDecoderInterface $refreshTokenDecoder,
        private readonly RefreshTokenManager $refreshTokenManager,
        private readonly RefreshTokenValidatorInterface $refreshTokenValidator,
    ) {
    }

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function refresh(string $data): RefreshToken
    {
        $decodedToken = $this->refreshTokenDecoder->decode($data);
        $token = $this->refreshTokenManager->findByToken($decodedToken);
        $this->refreshTokenValidator->validate($token);
        $newToken = $this->refreshTokenManager->create($token->getUserIdentifier());
        $this->refreshTokenManager->delete($token);

        return $newToken;
    }
}
