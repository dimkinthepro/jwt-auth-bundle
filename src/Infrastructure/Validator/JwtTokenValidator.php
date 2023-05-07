<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Validator;

use DimkinThePro\JwtAuth\Application\Component\Provider\CryptoConfigurationProvider;
use DimkinThePro\JwtAuth\Application\Component\Provider\KeyProviderInterface;
use DimkinThePro\JwtAuth\Application\Component\Provider\PayloadForSignProviderInterface;
use DimkinThePro\JwtAuth\Application\Component\Validator\JwtTokenValidatorInterface;
use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;
use DimkinThePro\JwtAuth\Infrastructure\Exception\InvalidTokenException;
use DimkinThePro\JwtAuth\Infrastructure\Exception\JwtTokenExpiredException;
use DimkinThePro\JwtAuth\Infrastructure\Factory\DateTimeFactory;

class JwtTokenValidator implements JwtTokenValidatorInterface
{
    private const SIGNATURE_CORRECT_CODE = 1;

    public function __construct(
        private readonly KeyProviderInterface $keyProvider,
        private readonly CryptoConfigurationProvider $cryptoConfigurationProvider,
        private readonly PayloadForSignProviderInterface $payloadForSignProvider,
        private readonly DateTimeFactory $dateTimeFactory,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function validate(JwtToken $token): void
    {
        if ($this->dateTimeFactory->getNowDate() > $token->getExpiredAt()) {
            throw new JwtTokenExpiredException('f2e2e73d-e620-49bd-97e8-07e4954f6096 JWT token expired');
        }

        $publicKey = $this->keyProvider->getPublicKey();
        $digestAlgorithm = $this->cryptoConfigurationProvider->getDigestAlgorithm($token->getAlgorithm());
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
