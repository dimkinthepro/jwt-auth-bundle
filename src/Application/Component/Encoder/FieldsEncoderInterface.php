<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Encoder;

interface FieldsEncoderInterface
{
    public function encode(string $data): string;
}
