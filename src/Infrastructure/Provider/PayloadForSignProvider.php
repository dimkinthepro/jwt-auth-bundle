<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Provider;

use Dimkinthepro\JwtAuth\Application\Component\Encoder\FieldsEncoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\PayloadForSignProviderInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;

readonly class PayloadForSignProvider implements PayloadForSignProviderInterface
{
    public function __construct(
        private FieldsEncoderInterface $fieldsEncoder,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function getPayload(JwtToken $jwtToken): string
    {
        // For decoded tokens verify against the exact bytes that were signed (RFC 7515):
        // re-serialization would break on unknown claims or a different JSON key order
        $signingInput = $jwtToken->getSigningInput();
        if (null !== $signingInput) {
            return $signingInput;
        }

        $header = $this->encode($jwtToken->getHeader());
        $payload = $this->encode($jwtToken->getPayload());

        return \sprintf('%s.%s', $header, $payload);
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
