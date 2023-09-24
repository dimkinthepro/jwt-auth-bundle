<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature;

use App\Domain\Security\AuthUserInterface;
use Faker\Factory;
use Symfony\Component\Uid\Uuid;

trait MockFixtureTrait
{
    protected function getAuthUser(
        Uuid $uuid = null
    ): AuthUserInterface {
        $user = $this->createMock(AuthUserInterface::class);
        $user->method('getUuid')->willReturn($uuid ?? Uuid::v6());
        $user->method('getEmail')->willReturn(Factory::create()->email());

        return $user;
    }
}
