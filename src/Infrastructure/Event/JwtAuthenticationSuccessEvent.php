<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched on successful login before the response is built:
 * listeners may enrich the response data (e.g. add user details).
 */
final class JwtAuthenticationSuccessEvent extends Event
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private array $data,
        private readonly UserInterface $user,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
