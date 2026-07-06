<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Persistence;

use Dimkinthepro\JwtAuth\Application\Component\Persistence\TransactionManagerInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class DoctrineTransactionManager implements TransactionManagerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function transactional(\Closure $operation): mixed
    {
        return $this->entityManager->wrapInTransaction($operation);
    }
}
