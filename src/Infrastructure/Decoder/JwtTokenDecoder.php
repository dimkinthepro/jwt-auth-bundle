<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Decoder;

use DimkinThePro\JwtAuth\Application\Component\Decoder\FieldsDecoderInterface;
use DimkinThePro\JwtAuth\Application\Component\Decoder\JwtTokenDecoderInterface;
use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;
use DimkinThePro\JwtAuth\Domain\Enum\AlgorithmEnum;
use DimkinThePro\JwtAuth\Domain\Enum\TokenDictionaryEnum;
use DimkinThePro\JwtAuth\Domain\Enum\TokenTypeEnum;
use DimkinThePro\JwtAuth\Infrastructure\Exception\InvalidTokenException;
use DimkinThePro\JwtAuth\Infrastructure\Factory\DateTimeFactory;

class JwtTokenDecoder implements JwtTokenDecoderInterface
{
    public function __construct(
        private readonly FieldsDecoderInterface $fieldsDecoder,
        private readonly DateTimeFactory $dateTimeFactory
    ) {
    }

    /**
     * {@inheritDoc}
     */
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
