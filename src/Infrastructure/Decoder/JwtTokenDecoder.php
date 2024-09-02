<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Decoder;

use Dimkinthepro\JwtAuth\Application\Component\Decoder\FieldsDecoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Decoder\JwtTokenDecoderInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenDictionaryEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\InvalidTokenException;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;

class JwtTokenDecoder implements JwtTokenDecoderInterface
{
    public function __construct(
        private readonly FieldsDecoderInterface $fieldsDecoder,
        private readonly DateTimeFactory $dateTimeFactory
    ) {
    }

    public function decode(string $encodedToken): JwtToken
    {
        try {
            $parts = explode('.', $encodedToken);
            $header = json_decode($this->fieldsDecoder->decode($parts[0]), true, 512, \JSON_THROW_ON_ERROR);
            $payload = json_decode($this->fieldsDecoder->decode($parts[1]), true, 512, \JSON_THROW_ON_ERROR);
            $signature = $this->fieldsDecoder->decode($parts[2]);

            $algorithm = AlgorithmEnum::from($header[TokenDictionaryEnum::ALGORITHM->value]);
            $type = TokenTypeEnum::from($header[TokenDictionaryEnum::TYPE->value]);
            $identifier = $payload[TokenDictionaryEnum::IDENTIFIER->value];
            $issuedAt = $payload[TokenDictionaryEnum::ISSUED_AT->value];
            $expiredAt = $payload[TokenDictionaryEnum::EXPIRED_AT->value];

            $token = new JwtToken(
                $algorithm,
                $type,
                $identifier,
                $this->dateTimeFactory->getNowDate($issuedAt),
                $this->dateTimeFactory->getNowDate($expiredAt)
            );
            $token->setSignature($signature);

            return $token;
        } catch (\Throwable $e) {
            throw new InvalidTokenException(sprintf(
                'a6dce48f-83dd-4a18-9f6e-d7afd7cf91ff Invalid token error: "%s"',
                $e->getMessage()
            ));
        }
    }
}
