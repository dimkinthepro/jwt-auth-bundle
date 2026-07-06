<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Base class for authentication failure events: listeners may replace the default response.
 */
abstract class JwtAuthenticationFailureEvent extends Event
{
    public function __construct(
        private Response $response,
        private readonly ?AuthenticationException $exception = null,
    ) {
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function getException(): ?AuthenticationException
    {
        return $this->exception;
    }
}
