<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Factory;

use Dimkinthepro\JwtAuth\Application\Component\Encoder\FieldsEncoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Factory\RefreshTokenFactoryInterface;
use Dimkinthepro\JwtAuth\Application\Component\Hasher\RefreshTokenHasherInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\DeviceContextProviderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\RefreshTokenGeneratorInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

readonly class RefreshTokenFactory implements RefreshTokenFactoryInterface
{
    private const SESSION_ID_BYTES = 16;

    public function __construct(
        private int $authRefreshTokenTtl,
        private RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private RefreshTokenHasherInterface $refreshTokenHasher,
        private FieldsEncoderInterface $fieldsEncoder,
        private DateTimeFactory $dateTimeFactory,
        private DeviceContextProviderInterface $deviceContextProvider,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function create(string $userIdentifier): RefreshToken
    {
        $now = $this->dateTimeFactory->getNowDate(time());
        $deviceContext = $this->deviceContextProvider->getDeviceContext();

        return $this->buildToken(
            $userIdentifier,
            bin2hex(random_bytes(self::SESSION_ID_BYTES)),
            $now,
            $now,
            $deviceContext->deviceName,
            $deviceContext->userAgent,
            $deviceContext->ip,
        );
    }

    /**
     * Issues a new token for the same device session: the session identity is inherited,
     * the device context is refreshed from the current request.
     *
     * @throws \Exception
     */
    public function rotate(RefreshToken $previousToken): RefreshToken
    {
        $deviceContext = $this->deviceContextProvider->getDeviceContext();

        return $this->buildToken(
            $previousToken->getUserIdentifier(),
            $previousToken->getSessionId(),
            $previousToken->getCreatedAt(),
            $this->dateTimeFactory->getNowDate(time()),
            $deviceContext->deviceName ?? $previousToken->getDeviceName(),
            $deviceContext->userAgent ?? $previousToken->getUserAgent(),
            $deviceContext->ip ?? $previousToken->getIp(),
        );
    }

    /**
     * @throws \Exception
     */
    private function buildToken(
        string $userIdentifier,
        string $sessionId,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $lastUsedAt,
        ?string $deviceName,
        ?string $userAgent,
        ?string $ip,
    ): RefreshToken {
        $rawToken = $this->refreshTokenGenerator->generate();
        $validDate = $this->dateTimeFactory->getNowDate(time() + $this->authRefreshTokenTtl);

        $refreshToken = new RefreshToken(
            $this->refreshTokenHasher->hash($rawToken),
            $userIdentifier,
            $validDate,
            $sessionId,
            $createdAt,
            $lastUsedAt,
            $deviceName,
            $userAgent,
            $ip,
        );
        $refreshToken->setEncodedToken($this->fieldsEncoder->encode($rawToken));

        return $refreshToken;
    }
}
