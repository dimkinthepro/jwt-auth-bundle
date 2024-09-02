<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Encoder;

use Dimkinthepro\JwtAuth\Application\Component\Encoder\FieldsEncoderInterface;

class Base64FieldsEncoder implements FieldsEncoderInterface
{
    public function encode(string $data): string
    {
        return base64_encode($data);
    }
}
