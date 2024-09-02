<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Manager;

use Dimkinthepro\JwtAuth\Application\Component\Encoder\RefreshTokenEncoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Application\Component\Factory\RefreshTokenFactoryInterface;
use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenReadRepositoryInterface;
use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenWriteRepositoryInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

class RefreshTokenManager
{
    public function __construct(
        private readonly RefreshTokenFactoryInterface $refreshTokenFactory,
        private readonly RefreshTokenEncoderInterface $refreshTokenEncoder,
        private readonly RefreshTokenReadRepositoryInterface $refreshTokenReadRepository,
        private readonly RefreshTokenWriteRepositoryInterface $refreshTokenWriteRepository,
    ) {
    }

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function findByToken(string $token): RefreshToken
    {
        return $this->refreshTokenReadRepository->findByTokenOrThrowException($token);
    }

    public function create(string $userIdentifier): RefreshToken
    {
        $refreshToken = $this->createRefreshToken($userIdentifier);
        $this->refreshTokenWriteRepository->save($refreshToken);
        $this->refreshTokenEncoder->encode($refreshToken);

        return $refreshToken;
    }

    public function delete(RefreshToken $token): void
    {
        $this->refreshTokenWriteRepository->delete($token);
    }

    private function createRefreshToken(string $userIdentifier): RefreshToken
    {
        while (true) {
            $refreshToken = $this->refreshTokenFactory->create($userIdentifier);
            $existingToken = $this->refreshTokenReadRepository->findByToken($refreshToken->getToken());
            if (null === $existingToken) {
                return $refreshToken;
            }
        }
    }
}
