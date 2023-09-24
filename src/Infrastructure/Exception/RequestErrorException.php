<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Exception;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RequestErrorException extends \Exception implements ErrorListExceptionInterface
{
    private array $errors = [];

    public function __construct(
        string $message = 'Incorrect parameters',
        int $code = Response::HTTP_UNPROCESSABLE_ENTITY,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function setViolationList(ConstraintViolationListInterface $violationList): void
    {
        /** @var ConstraintViolation $item */
        foreach ($violationList as $item) {
            $this->errors[$item->getPropertyPath()] = $item->getMessage();
        }
    }

    public function setFormErrors(FormErrorIterator $errors): void
    {
        /** @var FormError $error */
        foreach ($errors as $error) {
            $this->errors[(string) $error->getOrigin()->getPropertyPath()] = $error->getMessage();
        }
    }

    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
