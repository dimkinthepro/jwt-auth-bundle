<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Repository;

use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenReadRepositoryInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\RefreshTokenNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RefreshToken>
 */
class RefreshTokenReadRepository extends ServiceEntityRepository implements RefreshTokenReadRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function findByTokenHash(string $tokenHash): ?RefreshToken
    {
        return $this->findOneBy(['tokenHash' => $tokenHash]);
    }

    public function findBySessionId(string $sessionId): ?RefreshToken
    {
        return $this->findOneBy(['sessionId' => $sessionId]);
    }

    public function findAllByUserIdentifier(string $userIdentifier): array
    {
        return $this->findBy(['userIdentifier' => $userIdentifier], ['lastUsedAt' => 'DESC']);
    }

    public function findAllByUserIdentifierForUpdate(string $userIdentifier): array
    {
        return $this->createQueryBuilder('rt')
            ->where('rt.userIdentifier = :userIdentifier')
            ->setParameter('userIdentifier', $userIdentifier)
            ->orderBy('rt.lastUsedAt', 'DESC')
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->getResult();
    }

    public function findByTokenHashOrThrowException(string $tokenHash): RefreshToken
    {
        $refreshToken = $this->findByTokenHash($tokenHash);
        if (null === $refreshToken) {
            throw new RefreshTokenNotFoundException('Refresh token not found');
        }

        return $refreshToken;
    }
}
