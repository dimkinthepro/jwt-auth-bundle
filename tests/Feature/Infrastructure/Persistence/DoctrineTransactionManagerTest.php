<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Infrastructure\Persistence;

use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenReadRepositoryInterface;
use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenWriteRepositoryInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Infrastructure\Persistence\DoctrineTransactionManager;
use Dimkinthepro\JwtAuth\Tests\Feature\ResetDatabaseTrait;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineTransactionManagerTest extends KernelTestCase
{
    use ResetDatabaseTrait;

    private DoctrineTransactionManager $transactionManager;
    private RefreshTokenReadRepositoryInterface $readRepository;
    private RefreshTokenWriteRepositoryInterface $writeRepository;

    protected function setUp(): void
    {
        /** @var DoctrineTransactionManager $transactionManager */
        $transactionManager = self::getContainer()->get(DoctrineTransactionManager::class);
        $this->transactionManager = $transactionManager;

        /** @var RefreshTokenReadRepositoryInterface $readRepository */
        $readRepository = self::getContainer()->get(RefreshTokenReadRepositoryInterface::class);
        $this->readRepository = $readRepository;

        /** @var RefreshTokenWriteRepositoryInterface $writeRepository */
        $writeRepository = self::getContainer()->get(RefreshTokenWriteRepositoryInterface::class);
        $this->writeRepository = $writeRepository;

        $this->resetDatabase();
    }

    public function testSuccessfulOperationIsCommitted(): void
    {
        $token = $this->createToken();

        $result = $this->transactionManager->transactional(function () use ($token): string {
            $this->writeRepository->save($token);

            return 'result';
        });

        self::assertEquals('result', $result);
        self::assertNotNull($this->readRepository->findByTokenHash($token->getTokenHash()));
    }

    public function testFailedOperationIsRolledBack(): void
    {
        $token = $this->createToken();

        try {
            $this->transactionManager->transactional(function () use ($token): void {
                $this->writeRepository->save($token);

                throw new \RuntimeException('Operation failed after save');
            });
            self::fail('Expected exception was not thrown');
        } catch (\RuntimeException $e) {
        }

        self::assertNull($this->readRepository->findByTokenHash($token->getTokenHash()));
    }

    private function createToken(): RefreshToken
    {
        return new RefreshToken(
            hash('sha256', bin2hex(random_bytes(128))),
            Factory::create()->email(),
            new \DateTimeImmutable('+1 hour'),
            bin2hex(random_bytes(16)),
            new \DateTimeImmutable('-1 minute'),
            new \DateTimeImmutable()
        );
    }
}
