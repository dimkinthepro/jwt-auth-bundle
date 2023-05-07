<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Encoder;

use DimkinThePro\JwtAuth\Application\Component\Encoder\FieldsEncoderInterface;

class Base64FieldsEncoder implements FieldsEncoderInterface
{
    public function encode(string $data): string
    {
        return base64_encode($data);
    }
}
