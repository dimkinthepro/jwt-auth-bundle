<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Repository;

use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenReadRepositoryInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\RefreshTokenNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function findByToken(string $token): ?RefreshToken
    {
        return $this->findOneBy(['token' => $token]);
    }

    public function findByTokenOrThrowException(string $token): RefreshToken
    {
        $token = $this->findOneBy(['token' => $token]);
        if (null === $token) {
            throw new RefreshTokenNotFoundException(sprintf(
                'fc2c328e-bfd4-49b2-bbf7-1a675d3593ee Refresh token not found by: "%s"',
                $token
            ));
        }

        return $token;
    }
}
