<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Domain\Entity;

class RefreshToken
{
    private ?int $id = null;
    private string $encodedToken;

    public function __construct(
        private readonly string $token,
        private readonly string $userIdentifier,
        private readonly \DateTimeImmutable $validUntil,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function getValidUntil(): \DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function getEncodedToken(): string
    {
        return $this->encodedToken;
    }

    public function setEncodedToken(string $encodedToken): void
    {
        $this->encodedToken = $encodedToken;
    }
}
