<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\EventListener;

use Dimkinthepro\JwtAuth\Infrastructure\Event\JwtAuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class TestAuthenticationSuccessListener
{
    public const USER_EMAIL_KEY = 'userEmail';

    public function __invoke(JwtAuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();
        $data[self::USER_EMAIL_KEY] = $event->getUser()->getUserIdentifier();
        $event->setData($data);
    }
}
