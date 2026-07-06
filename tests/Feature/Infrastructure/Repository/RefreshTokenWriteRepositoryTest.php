<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Infrastructure\Repository;

use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenWriteRepositoryInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\RefreshTokenNotFoundException;
use Dimkinthepro\JwtAuth\Tests\Feature\ResetDatabaseTrait;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RefreshTokenWriteRepositoryTest extends KernelTestCase
{
    use ResetDatabaseTrait;

    public function testDeleteOfAlreadyDeletedTokenIsRejected(): void
    {
        $this->resetDatabase();

        /** @var RefreshTokenWriteRepositoryInterface $writeRepository */
        $writeRepository = self::getContainer()->get(RefreshTokenWriteRepositoryInterface::class);
        $token = new RefreshToken(
            hash('sha256', bin2hex(random_bytes(128))),
            Factory::create()->email(),
            new \DateTimeImmutable('+1 hour'),
            bin2hex(random_bytes(16)),
            new \DateTimeImmutable('-1 minute'),
            new \DateTimeImmutable()
        );
        $writeRepository->save($token);

        $writeRepository->delete($token);

        self::expectException(RefreshTokenNotFoundException::class);

        $writeRepository->delete($token);
    }
}
