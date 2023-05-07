<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Factory;

use DimkinThePro\JwtAuth\Application\Component\Factory\JwtTokenFactoryInterface;
use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;
use DimkinThePro\JwtAuth\Domain\Enum\AlgorithmEnum;
use DimkinThePro\JwtAuth\Domain\Enum\TokenTypeEnum;

class JwtTokenFactory implements JwtTokenFactoryInterface
{
    public function __construct(
        private readonly string $authAlgorithm,
        private readonly int $authJwtTokenTtl,
        private readonly DateTimeFactory $dateTimeFactory
    ) {
    }

    public function create(string $userIdentifier): JwtToken
    {
        $issuedAt = time();
        $expiredAt = $issuedAt + $this->authJwtTokenTtl;

        return new JwtToken(
            AlgorithmEnum::from($this->authAlgorithm),
            TokenTypeEnum::JWT,
            $userIdentifier,
            $this->dateTimeFactory->getNowDate($issuedAt),
            $this->dateTimeFactory->getNowDate($expiredAt),
        );
    }
}
