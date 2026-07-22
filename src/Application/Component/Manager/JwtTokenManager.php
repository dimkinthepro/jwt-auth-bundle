<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Manager;

use Dimkinthepro\JwtAuth\Application\Component\Encoder\JwtTokenEncoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Factory\JwtTokenFactoryInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\JwtTokenSignerInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;

readonly class JwtTokenManager implements JwtTokenManagerInterface
{
    public function __construct(
        private JwtTokenFactoryInterface $jwtTokenFactory,
        private JwtTokenSignerInterface $jwtTokenSigner,
        private JwtTokenEncoderInterface $jwtTokenEncoder,
    ) {
    }

    public function create(string $userIdentifier, ?string $sessionId = null): JwtToken
    {
        $token = $this->jwtTokenFactory->create($userIdentifier, $sessionId);
        $this->jwtTokenSigner->sign($token);
        $this->jwtTokenEncoder->encode($token);

        return $token;
    }
}
