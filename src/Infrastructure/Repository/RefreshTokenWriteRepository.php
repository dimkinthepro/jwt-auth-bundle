<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Repository;

use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenWriteRepositoryInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\RefreshTokenNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RefreshToken>
 */
class RefreshTokenWriteRepository extends ServiceEntityRepository implements RefreshTokenWriteRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function save(RefreshToken $token): void
    {
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
    }

    public function delete(RefreshToken $token): void
    {
        $deletedCount = $this->getEntityManager()
            ->createQuery(\sprintf('DELETE FROM %s rt WHERE rt.id = :id', RefreshToken::class))
            ->setParameter('id', $token->getId())
            ->execute();

        // Zero affected rows means the token was spent by a concurrent request: treat it as already used
        if (0 === $deletedCount) {
            throw new RefreshTokenNotFoundException(\sprintf(
                '3f7a5e51-6a83-4f9e-8ae0-6a2b0e1b7a90 Refresh token already deleted, id: "%d"',
                (int) $token->getId()
            ));
        }
    }

    public function deleteExpired(\DateTimeImmutable $expiredBefore): int
    {
        return $this->getEntityManager()
            ->createQuery(\sprintf('DELETE FROM %s rt WHERE rt.validUntil < :expiredBefore', RefreshToken::class))
            ->setParameter('expiredBefore', $expiredBefore)
            ->execute();
    }

    public function deleteAll(array $tokens): int
    {
        $ids = array_filter(array_map(
            static fn (RefreshToken $token): ?int => $token->getId(),
            $tokens
        ));

        if ([] === $ids) {
            return 0;
        }

        return $this->getEntityManager()
            ->createQuery(\sprintf('DELETE FROM %s rt WHERE rt.id IN (:ids)', RefreshToken::class))
            ->setParameter('ids', $ids)
            ->execute();
    }
}
