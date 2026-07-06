<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Response;

use Dimkinthepro\JwtAuth\Infrastructure\Enum\ResponseEnum;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ResponseTrait
{
    protected function successJson(array $data): Response
    {
        return new JsonResponse([
            ResponseEnum::DATA->value => $data,
        ]);
    }

    protected function failJson(array $errors, int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY): Response
    {
        return new JsonResponse([
            ResponseEnum::ERRORS->value => $errors,
        ], $statusCode);
    }
}
