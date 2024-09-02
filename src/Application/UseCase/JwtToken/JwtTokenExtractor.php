<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\UseCase\JwtToken;

use Dimkinthepro\JwtAuth\Application\Component\Decoder\JwtTokenDecoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Application\Component\Validator\JwtTokenValidatorInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;

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
