<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\UseCase\Token;

use Dimkinthepro\JwtAuth\Application\Component\Decoder\RefreshTokenDecoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Application\Component\Manager\JwtTokenManagerInterface;
use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManagerInterface;
use Dimkinthepro\JwtAuth\Application\Component\Persistence\TransactionManagerInterface;
use Dimkinthepro\JwtAuth\Application\Component\Validator\RefreshTokenValidatorInterface;
use Dimkinthepro\JwtAuth\Application\DTO\TokenPair;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

final readonly class TokenPairRefresher
{
    public function __construct(
        private JwtTokenManagerInterface $jwtTokenManager,
        private RefreshTokenDecoderInterface $refreshTokenDecoder,
        private RefreshTokenManagerInterface $refreshTokenManager,
        private RefreshTokenValidatorInterface $refreshTokenValidator,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function getPairByRefreshToken(string $refreshTokenValue): TokenPair
    {
        $refreshToken = $this->rotateRefreshToken($refreshTokenValue);
        $jwtToken = $this->jwtTokenManager->create(
            $refreshToken->getUserIdentifier(),
            $refreshToken->getSessionId()
        );

        return new TokenPair(
            token: $jwtToken,
            refreshToken: $refreshToken,
        );
    }

    /**
     * @throws JwtAuthExceptionInterface
     */
    private function rotateRefreshToken(string $refreshToken): RefreshToken
    {
        return $this->transactionManager->transactional(function () use ($refreshToken): RefreshToken {
            $decodedToken = $this->refreshTokenDecoder->decode($refreshToken);
            $token = $this->refreshTokenManager->findByToken($decodedToken);
            $this->refreshTokenValidator->validate($token);
            $this->refreshTokenManager->delete($token);

            return $this->refreshTokenManager->rotate($token);
        });
    }
}
