<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Repository;

use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenWriteRepositoryInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
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
        $this->_em->persist($token);
        $this->_em->flush();
    }

    public function delete(RefreshToken $token): void
    {
        $this->_em->remove($token);
        $this->_em->flush();
    }
}
