<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Domain\Entity;

use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenDictionaryEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;

class JwtToken
{
    private string $signature;
    private string $encodedToken;
    private ?string $signingInput = null;

    /**
     * @param array<string, mixed> $customClaims
     */
    public function __construct(
        private readonly AlgorithmEnum $algorithm,
        private readonly TokenTypeEnum $type,
        private readonly string $userIdentifier,
        private readonly \DateTimeImmutable $issuedAt,
        private readonly \DateTimeImmutable $expiredAt,
        private readonly array $customClaims = []
    ) {
    }

    public function getAlgorithm(): AlgorithmEnum
    {
        return $this->algorithm;
    }

    public function getType(): TokenTypeEnum
    {
        return $this->type;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function getIssuedAt(): \DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function getExpiredAt(): \DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function getHeader(): array
    {
        return [
            TokenDictionaryEnum::ALGORITHM->value => $this->algorithm->value,
            TokenDictionaryEnum::TYPE->value => $this->type->value,
        ];
    }

    public function getPayload(): array
    {
        // Reserved claims are merged last so custom claims can never override them
        return array_merge($this->customClaims, [
            TokenDictionaryEnum::IDENTIFIER->value => $this->userIdentifier,
            TokenDictionaryEnum::ISSUED_AT->value => $this->issuedAt->getTimestamp(),
            TokenDictionaryEnum::EXPIRED_AT->value => $this->expiredAt->getTimestamp(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getCustomClaims(): array
    {
        return $this->customClaims;
    }

    public function getClaim(string $name): mixed
    {
        return $this->customClaims[$name] ?? null;
    }

    /**
     * Raw "encodedHeader.encodedPayload" string the signature was computed over (RFC 7515 signing input).
     * Available only on tokens restored by the decoder.
     */
    public function getSigningInput(): ?string
    {
        return $this->signingInput;
    }

    public function setSigningInput(string $signingInput): void
    {
        $this->signingInput = $signingInput;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
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
