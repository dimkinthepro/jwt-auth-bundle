<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Factory;

use Dimkinthepro\JwtAuth\Application\Component\Event\JwtTokenCreatedEvent;
use Dimkinthepro\JwtAuth\Application\Component\Factory\JwtTokenFactoryInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenDictionaryEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class JwtTokenFactory implements JwtTokenFactoryInterface
{
    private const TOKEN_ID_BYTES = 16;

    public function __construct(
        private string $authAlgorithm,
        private int $authJwtTokenTtl,
        private DateTimeFactory $dateTimeFactory,
        private EventDispatcherInterface $eventDispatcher,
        private ?string $authIssuer = null,
        private ?string $authAudience = null,
    ) {
    }

    public function create(string $userIdentifier, ?string $sessionId = null): JwtToken
    {
        $issuedAt = time();
        $expiredAt = $issuedAt + $this->authJwtTokenTtl;

        $event = new JwtTokenCreatedEvent($userIdentifier, $this->buildStandardClaims($issuedAt, $sessionId));
        $this->eventDispatcher->dispatch($event);

        return new JwtToken(
            AlgorithmEnum::from($this->authAlgorithm),
            TokenTypeEnum::JWT,
            $userIdentifier,
            $this->dateTimeFactory->getNowDate($issuedAt),
            $this->dateTimeFactory->getNowDate($expiredAt),
            $event->getClaims(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStandardClaims(int $issuedAt, ?string $sessionId): array
    {
        $claims = [
            TokenDictionaryEnum::NOT_BEFORE->value => $issuedAt,
            TokenDictionaryEnum::TOKEN_ID->value => bin2hex(random_bytes(self::TOKEN_ID_BYTES)),
        ];

        if (null !== $this->authIssuer) {
            $claims[TokenDictionaryEnum::ISSUER->value] = $this->authIssuer;
        }
        if (null !== $this->authAudience) {
            $claims[TokenDictionaryEnum::AUDIENCE->value] = $this->authAudience;
        }
        if (null !== $sessionId) {
            $claims[TokenDictionaryEnum::SESSION_ID->value] = $sessionId;
        }

        return $claims;
    }
}
