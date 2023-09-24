<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Decoder;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;

interface FieldsDecoderInterface
{
    /**
     * @throws JwtAuthExceptionInterface
     */
    public function decode(string $encodedToken): string;
}
