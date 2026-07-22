<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\UseCase\Token;

use Dimkinthepro\JwtAuth\Application\Component\Blocklist\TokenBlocklistInterface;
use Dimkinthepro\JwtAuth\Application\Component\Decoder\JwtTokenDecoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Event\JwtTokenDecodedEvent;
use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Application\Component\Validator\JwtTokenValidatorInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenDictionaryEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\InvalidTokenException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class JwtTokenDecoder
{
    public function __construct(
        private readonly JwtTokenDecoderInterface $tokenDecoder,
        private readonly JwtTokenValidatorInterface $jwtTokenValidator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TokenBlocklistInterface $tokenBlocklist,
        private readonly bool $authBlocklistEnabled = false,
    ) {
    }

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function decodeTokenFromString(string $token): JwtToken
    {
        $jwtToken = $this->tokenDecoder->decode($token);
        $this->jwtTokenValidator->validate($jwtToken);
        $this->validateAgainstBlocklist($jwtToken);

        $event = new JwtTokenDecodedEvent($jwtToken);
        $this->eventDispatcher->dispatch($event);
        if ($event->isMarkedAsInvalid()) {
            throw new InvalidTokenException('7d4c25e8-0f6a-4c3b-9d21-58b7f0a3c9e6 Token rejected by listener');
        }

        return $jwtToken;
    }

    /**
     * @throws InvalidTokenException
     */
    private function validateAgainstBlocklist(JwtToken $jwtToken): void
    {
        if (false === $this->authBlocklistEnabled) {
            return;
        }

        // A token without a session id could never be revoked, so it must not bypass the blocklist
        $sessionId = $jwtToken->getClaim(TokenDictionaryEnum::SESSION_ID->value);
        if (false === \is_string($sessionId) || '' === $sessionId) {
            throw new InvalidTokenException('b4d3f0a9-1e6c-47b2-8d5f-9a0c3e7b2f61 Token has no session id claim');
        }

        if ($this->tokenBlocklist->isBlocked($sessionId)) {
            throw new InvalidTokenException('6b8e1f4a-9c27-4d05-8a63-0f5d2c7b9e18 Token revoked');
        }
    }
}
