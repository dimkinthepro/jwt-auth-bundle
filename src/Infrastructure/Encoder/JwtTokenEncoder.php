<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Encoder;

use Dimkinthepro\JwtAuth\Application\Component\Encoder\FieldsEncoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Encoder\JwtTokenEncoderInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;

class JwtTokenEncoder implements JwtTokenEncoderInterface
{
    public function __construct(
        private readonly FieldsEncoderInterface $fieldsEncoder,
    ) {
    }

    public function encode(JwtToken $token): void
    {
        $header = $this->encodeField($token->getHeader());
        $payload = $this->encodeField($token->getPayload());
        $encodedToken = sprintf('%s.%s.%s', $header, $payload, $token->getSignature());
        $token->setEncodedToken($encodedToken);
    }

    /**
     * @throws \JsonException
     */
    private function encodeField(array|string $field): string
    {
        if (true === \is_array($field)) {
            $field = json_encode($field, \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR);
        }

        return $this->fieldsEncoder->encode($field);
    }
}
