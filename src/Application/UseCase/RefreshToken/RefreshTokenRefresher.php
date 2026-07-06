<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\UseCase\RefreshToken;

use Dimkinthepro\JwtAuth\Application\Component\Decoder\RefreshTokenDecoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManager;
use Dimkinthepro\JwtAuth\Application\Component\Persistence\TransactionManagerInterface;
use Dimkinthepro\JwtAuth\Application\Component\Validator\RefreshTokenValidatorInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

class RefreshTokenRefresher
{
    public function __construct(
        private readonly RefreshTokenDecoderInterface $refreshTokenDecoder,
        private readonly RefreshTokenManager $refreshTokenManager,
        private readonly RefreshTokenValidatorInterface $refreshTokenValidator,
        private readonly TransactionManagerInterface $transactionManager,
    ) {
    }

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function refresh(string $data): RefreshToken
    {
        return $this->transactionManager->transactional(function () use ($data): RefreshToken {
            $decodedToken = $this->refreshTokenDecoder->decode($data);
            $token = $this->refreshTokenManager->findByToken($decodedToken);
            $this->refreshTokenValidator->validate($token);
            $this->refreshTokenManager->delete($token);

            return $this->refreshTokenManager->rotate($token);
        });
    }
}
