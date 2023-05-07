<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Message\Query;

use DimkinThePro\CommandQuery\Query\QueryHandlerInterface;
use DimkinThePro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use DimkinThePro\JwtAuth\Application\UseCase\JwtToken\JwtTokenExtractor;
use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;

class ExtractJwtTokenQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly JwtTokenExtractor $extractor
    ) {
    }

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function __invoke(ExtractJwtTokenQuery $query): JwtToken
    {
        return $this->extractor->extract($query->token);
    }
}
