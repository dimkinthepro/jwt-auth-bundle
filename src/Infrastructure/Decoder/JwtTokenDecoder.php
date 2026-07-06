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

readonly class JwtTokenDecoder implements JwtTokenDecoderInterface
{
    private const TOKEN_PARTS_COUNT = 3;

    public function __construct(
        private FieldsDecoderInterface $fieldsDecoder,
        private DateTimeFactory $dateTimeFactory
    ) {
    }

    public function decode(string $encodedToken): JwtToken
    {
        $parts = explode('.', $encodedToken);
        if (self::TOKEN_PARTS_COUNT !== \count($parts)) {
            throw new InvalidTokenException(\sprintf(
                '0d8276a1-52ad-4c25-a2b7-4a7f37f6a1ce Invalid token structure: expected %d parts, got %d',
                self::TOKEN_PARTS_COUNT,
                \count($parts)
            ));
        }

        try {
            $header = json_decode($this->fieldsDecoder->decode($parts[0]), true, 512, \JSON_THROW_ON_ERROR);
            $payload = json_decode($this->fieldsDecoder->decode($parts[1]), true, 512, \JSON_THROW_ON_ERROR);
            $signature = $this->fieldsDecoder->decode($parts[2]);

            $algorithm = AlgorithmEnum::from($header[TokenDictionaryEnum::ALGORITHM->value]);
            $type = TokenTypeEnum::from($header[TokenDictionaryEnum::TYPE->value]);
            $identifier = $payload[TokenDictionaryEnum::IDENTIFIER->value];
            $issuedAt = $payload[TokenDictionaryEnum::ISSUED_AT->value];
            $expiredAt = $payload[TokenDictionaryEnum::EXPIRED_AT->value];
            $customClaims = array_diff_key($payload, array_flip([
                TokenDictionaryEnum::IDENTIFIER->value,
                TokenDictionaryEnum::ISSUED_AT->value,
                TokenDictionaryEnum::EXPIRED_AT->value,
            ]));

            $token = new JwtToken(
                $algorithm,
                $type,
                $identifier,
                $this->dateTimeFactory->getNowDate($issuedAt),
                $this->dateTimeFactory->getNowDate($expiredAt),
                $customClaims
            );
            $token->setSignature($signature);
            $token->setSigningInput(\sprintf('%s.%s', $parts[0], $parts[1]));

            return $token;
        } catch (\Throwable $e) {
            throw new InvalidTokenException(\sprintf(
                'a6dce48f-83dd-4a18-9f6e-d7afd7cf91ff Invalid token error: "%s"',
                $e->getMessage()
            ));
        }
    }
}
