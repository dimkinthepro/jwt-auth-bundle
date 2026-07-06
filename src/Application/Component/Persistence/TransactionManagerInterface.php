<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Persistence;

interface TransactionManagerInterface
{
    /**
     * Executes the operation atomically: it is rolled back entirely if an exception is thrown.
     *
     * @template T
     *
     * @param \Closure(): T $operation
     *
     * @return T
     */
    public function transactional(\Closure $operation): mixed;
}
