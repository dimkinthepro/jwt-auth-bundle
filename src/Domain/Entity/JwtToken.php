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

    public function __construct(
        private readonly AlgorithmEnum $algorithm,
        private readonly TokenTypeEnum $type,
        private readonly string $userIdentifier,
        private readonly \DateTimeImmutable $issuedAt,
        private readonly \DateTimeImmutable $expiredAt
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
        return [
            TokenDictionaryEnum::IDENTIFIER->value => $this->userIdentifier,
            TokenDictionaryEnum::ISSUED_AT->value => $this->issuedAt->getTimestamp(),
            TokenDictionaryEnum::EXPIRED_AT->value => $this->expiredAt->getTimestamp(),
        ];
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
