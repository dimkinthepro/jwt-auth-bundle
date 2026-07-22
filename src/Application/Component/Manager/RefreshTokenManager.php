<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Manager;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Application\Component\Exception\RefreshTokenCreationException;
use Dimkinthepro\JwtAuth\Application\Component\Factory\RefreshTokenFactoryInterface;
use Dimkinthepro\JwtAuth\Application\Component\Hasher\RefreshTokenHasherInterface;
use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenReadRepositoryInterface;
use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenWriteRepositoryInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\RefreshTokenNotFoundException;

class RefreshTokenManager implements RefreshTokenManagerInterface
{
    private const int CREATE_TOKEN_MAX_ATTEMPTS = 3;

    public function __construct(
        private readonly RefreshTokenFactoryInterface $refreshTokenFactory,
        private readonly RefreshTokenHasherInterface $refreshTokenHasher,
        private readonly RefreshTokenReadRepositoryInterface $refreshTokenReadRepository,
        private readonly RefreshTokenWriteRepositoryInterface $refreshTokenWriteRepository,
    ) {
    }

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function findByToken(string $rawToken): RefreshToken
    {
        $tokenHash = $this->refreshTokenHasher->hash($rawToken);

        return $this->refreshTokenReadRepository->findByTokenHashOrThrowException($tokenHash);
    }

    /**
     * Device sessions of the user, most recently used first.
     *
     * @return RefreshToken[]
     */
    public function findAllByUserIdentifier(string $userIdentifier): array
    {
        return $this->refreshTokenReadRepository->findAllByUserIdentifier($userIdentifier);
    }

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function create(string $userIdentifier): RefreshToken
    {
        $refreshToken = $this->generateUniqueToken(
            fn (): RefreshToken => $this->refreshTokenFactory->create($userIdentifier)
        );
        $this->refreshTokenWriteRepository->save($refreshToken);

        return $refreshToken;
    }

    /**
     * Issues a new token for the same device session as the previous one.
     *
     * @throws JwtAuthExceptionInterface
     */
    public function rotate(RefreshToken $previousToken): RefreshToken
    {
        $refreshToken = $this->generateUniqueToken(
            fn (): RefreshToken => $this->refreshTokenFactory->rotate($previousToken)
        );
        $this->refreshTokenWriteRepository->save($refreshToken);

        return $refreshToken;
    }

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function delete(RefreshToken $token): void
    {
        $this->refreshTokenWriteRepository->delete($token);
    }

    /**
     * Revokes a device session of the given user. A session of another user is reported
     * as not found to avoid leaking session identifiers.
     *
     * @throws JwtAuthExceptionInterface
     */
    public function revokeSession(string $sessionId, string $userIdentifier): void
    {
        $session = $this->refreshTokenReadRepository->findBySessionId($sessionId);
        if (null === $session || $session->getUserIdentifier() !== $userIdentifier) {
            throw new RefreshTokenNotFoundException(\sprintf(
                '8a3b2c9d-7e61-4f80-b5a4-2d9c1e0f6a37 Session not found: "%s"',
                $sessionId
            ));
        }

        $this->refreshTokenWriteRepository->delete($session);
    }

    /**
     * Device sessions of the user, locked for update until the transaction ends.
     * Must be called inside an active transaction.
     *
     * @return RefreshToken[]
     */
    public function findAllByUserIdentifierForUpdate(string $userIdentifier): array
    {
        return $this->refreshTokenReadRepository->findAllByUserIdentifierForUpdate($userIdentifier);
    }

    /**
     * @param RefreshToken[] $sessions
     *
     * @return int number of deleted sessions
     */
    public function deleteSessions(array $sessions): int
    {
        return $this->refreshTokenWriteRepository->deleteAll($sessions);
    }

    /**
     * @return int number of deleted tokens
     */
    public function deleteExpiredTokens(\DateTimeImmutable $expiredBefore): int
    {
        return $this->refreshTokenWriteRepository->deleteExpired($expiredBefore);
    }

    /**
     * @param \Closure(): RefreshToken $buildToken
     */
    private function generateUniqueToken(\Closure $buildToken): RefreshToken
    {
        for ($attempt = 1; $attempt <= self::CREATE_TOKEN_MAX_ATTEMPTS; ++$attempt) {
            $refreshToken = $buildToken();
            $existingToken = $this->refreshTokenReadRepository->findByTokenHash($refreshToken->getTokenHash());
            if (null === $existingToken) {
                return $refreshToken;
            }
        }

        throw new RefreshTokenCreationException(\sprintf(
            'Failed to generate a unique refresh token after %d attempts',
            self::CREATE_TOKEN_MAX_ATTEMPTS
        ));
    }
}
