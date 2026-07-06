<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Validator;

use Dimkinthepro\JwtAuth\Application\Component\Provider\CryptoConfigurationProvider;
use Dimkinthepro\JwtAuth\Application\Component\Provider\KeyProviderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\PayloadForSignProviderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Validator\JwtTokenValidatorInterface;
use Dimkinthepro\JwtAuth\DependencyInjection\Configuration;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenDictionaryEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\InvalidTokenException;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\JwtTokenExpiredException;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;

readonly class JwtTokenValidator implements JwtTokenValidatorInterface
{
    private const SIGNATURE_CORRECT_CODE = 1;

    public function __construct(
        private string $authAlgorithm,
        private KeyProviderInterface $keyProvider,
        private CryptoConfigurationProvider $cryptoConfigurationProvider,
        private PayloadForSignProviderInterface $payloadForSignProvider,
        private DateTimeFactory $dateTimeFactory,
        private int $authClockSkewLeeway = Configuration::CLOCK_SKEW_LEEWAY_VALUE,
        private ?string $authIssuer = null,
        private ?string $authAudience = null,
    ) {
    }

    public function validate(JwtToken $token): void
    {
        // Pin the algorithm to the configured one: the "alg" header is attacker-controlled input
        $configuredAlgorithm = AlgorithmEnum::from($this->authAlgorithm);
        if ($configuredAlgorithm !== $token->getAlgorithm()) {
            throw new InvalidTokenException('c6b0dbd7-5f45-4d51-bfaa-8c611badb355 Unexpected token algorithm');
        }

        $this->validateTimeClaims($token);
        $this->validateIssuer($token);
        $this->validateAudience($token);
        $this->validateSignature($token, $configuredAlgorithm);
    }

    private function validateTimeClaims(JwtToken $token): void
    {
        $now = $this->dateTimeFactory->getNowDate()->getTimestamp();

        if ($now > $token->getExpiredAt()->getTimestamp() + $this->authClockSkewLeeway) {
            throw new JwtTokenExpiredException('f2e2e73d-e620-49bd-97e8-07e4954f6096 JWT token expired');
        }

        if ($token->getIssuedAt()->getTimestamp() > $now + $this->authClockSkewLeeway) {
            throw new InvalidTokenException('e0f0942d-7ee9-4b41-91cf-0e0d78ea69a4 Token issued in the future');
        }

        $notBefore = $token->getClaim(TokenDictionaryEnum::NOT_BEFORE->value);
        if (null === $notBefore) {
            return;
        }
        if (false === is_numeric($notBefore) || (int) $notBefore > $now + $this->authClockSkewLeeway) {
            throw new InvalidTokenException('9e2f6c1b-6d0e-4a2f-8f70-27a54c1b2d18 Token not yet valid');
        }
    }

    private function validateIssuer(JwtToken $token): void
    {
        if (null === $this->authIssuer) {
            return;
        }

        if ($this->authIssuer !== $token->getClaim(TokenDictionaryEnum::ISSUER->value)) {
            throw new InvalidTokenException('5a70e8a4-9df6-4a30-b6a5-8dcd6f45c1f2 Unexpected token issuer');
        }
    }

    private function validateAudience(JwtToken $token): void
    {
        if (null === $this->authAudience) {
            return;
        }

        $audience = $token->getClaim(TokenDictionaryEnum::AUDIENCE->value);
        $audiences = \is_array($audience) ? $audience : [$audience];
        if (false === \in_array($this->authAudience, $audiences, true)) {
            throw new InvalidTokenException('1c9eddb1-8f5a-4f2b-b356-6c8e30ed86bd Unexpected token audience');
        }
    }

    private function validateSignature(JwtToken $token, AlgorithmEnum $algorithm): void
    {
        $publicKey = $this->keyProvider->getPublicKey();
        $digestAlgorithm = $this->cryptoConfigurationProvider->getDigestAlgorithm($algorithm);
        $payload = $this->payloadForSignProvider->getPayload($token);

        $result = openssl_verify(
            $payload,
            $token->getSignature(),
            $publicKey,
            $digestAlgorithm->value
        );

        if (self::SIGNATURE_CORRECT_CODE !== $result) {
            throw new InvalidTokenException('64ede6f3-dc35-4254-9734-1c6bd609806f Invalid token');
        }
    }
}
