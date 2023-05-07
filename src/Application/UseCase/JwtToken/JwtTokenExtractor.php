<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\UseCase\JwtToken;

use DimkinThePro\JwtAuth\Application\Component\Decoder\JwtTokenDecoderInterface;
use DimkinThePro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use DimkinThePro\JwtAuth\Application\Component\Validator\JwtTokenValidatorInterface;
use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;

class JwtTokenExtractor
{
    public function __construct(
        private readonly JwtTokenDecoderInterface $tokenDecoder,
        private readonly JwtTokenValidatorInterface $jwtTokenValidator,
    ) {
    }

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function extract(string $token): JwtToken
    {
        $jwtToken = $this->tokenDecoder->decode($token);
        $this->jwtTokenValidator->validate($jwtToken);

        return $jwtToken;
    }
}
