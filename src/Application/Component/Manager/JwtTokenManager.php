<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Manager;

use Dimkinthepro\JwtAuth\Application\Component\Encoder\JwtTokenEncoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Factory\JwtTokenFactoryInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\JwtTokenSignerInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;

class JwtTokenManager
{
    public function __construct(
        private readonly JwtTokenFactoryInterface $jwtTokenFactory,
        private readonly JwtTokenSignerInterface $jwtTokenSigner,
        private readonly JwtTokenEncoderInterface $jwtTokenEncoder,
    ) {
    }

    public function create(string $userIdentifier): JwtToken
    {
        $token = $this->jwtTokenFactory->create($userIdentifier);
        $this->jwtTokenSigner->sign($token);
        $this->jwtTokenEncoder->encode($token);

        return $token;
    }
}
