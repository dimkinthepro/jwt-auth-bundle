<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\EventListener;

use Dimkinthepro\JwtAuth\Application\Component\Event\JwtTokenCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class TestCustomClaimsListener
{
    public const ROLE_CLAIM = 'role';
    public const ROLE_VALUE = 'tester';

    public function __invoke(JwtTokenCreatedEvent $event): void
    {
        $claims = $event->getClaims();
        $claims[self::ROLE_CLAIM] = self::ROLE_VALUE;
        $event->setClaims($claims);
    }
}
