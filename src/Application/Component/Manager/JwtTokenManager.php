<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Manager;

use DimkinThePro\JwtAuth\Application\Component\Encoder\JwtTokenEncoderInterface;
use DimkinThePro\JwtAuth\Application\Component\Factory\JwtTokenFactoryInterface;
use DimkinThePro\JwtAuth\Application\Component\Provider\JwtTokenSignerInterface;
use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;

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
