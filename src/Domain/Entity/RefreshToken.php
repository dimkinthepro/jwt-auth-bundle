<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Domain\Entity;

class RefreshToken
{
    private ?int $id = null;
    private string $encodedToken;

    public function __construct(
        private readonly string $tokenHash,
        private readonly string $userIdentifier,
        private readonly \DateTimeImmutable $validUntil,
        private readonly string $sessionId,
        private readonly \DateTimeImmutable $createdAt,
        private readonly \DateTimeImmutable $lastUsedAt,
        private readonly ?string $deviceName = null,
        private readonly ?string $userAgent = null,
        private readonly ?string $ip = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function getValidUntil(): \DateTimeImmutable
    {
        return $this->validUntil;
    }

    /**
     * Stable identifier of the device session: survives token rotation.
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * When the session was created (login time); inherited on rotation.
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastUsedAt(): \DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function getDeviceName(): ?string
    {
        return $this->deviceName;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getIp(): ?string
    {
        return $this->ip;
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
