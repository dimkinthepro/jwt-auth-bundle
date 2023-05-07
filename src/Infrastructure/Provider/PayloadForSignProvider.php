<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Provider;

use DimkinThePro\JwtAuth\Application\Component\Encoder\FieldsEncoderInterface;
use DimkinThePro\JwtAuth\Application\Component\Provider\PayloadForSignProviderInterface;
use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;

class PayloadForSignProvider implements PayloadForSignProviderInterface
{
    public function __construct(
        private readonly FieldsEncoderInterface $fieldsEncoder,
        private readonly string $authPassphrase,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function getPayload(JwtToken $jwtToken): string
    {
        $header = $this->encode($jwtToken->getHeader());
        $payload = $this->encode($jwtToken->getPayload());

        return sprintf('%s.%s.%s', $header, $payload, $this->authPassphrase);
    }

    /**
     * @throws \JsonException
     */
    private function encode(array $data): string
    {
        $jsonData = json_encode($data, \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR);

        return $this->fieldsEncoder->encode($jsonData);
    }
}
