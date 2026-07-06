<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Controller;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Infrastructure\DTO\RefreshTokenFormDto;
use Dimkinthepro\JwtAuth\Infrastructure\Enum\TokenResponseEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Form\RefreshTokenForm;
use Dimkinthepro\JwtAuth\Infrastructure\Response\ResponseTrait;
use Dimkinthepro\JwtAuth\Infrastructure\Service\TokenServiceInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class TokenRefreshAction
{
    use ResponseTrait;

    public function __construct(
        private FormFactoryInterface $formFactory,
        private TokenServiceInterface $tokenService
    ) {
    }

    /**
     * @throws BadRequestHttpException
     */
    public function __invoke(Request $request): Response
    {
        try {
            $content = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new BadRequestHttpException('1a127ca3-3531-42eb-92fd-600fb5d06286 Invalid token');
        }

        if (false === \is_array($content)) {
            throw new BadRequestHttpException('726e3bdd-2b17-4b8a-b032-95bd4d3529c8 Invalid request body');
        }

        $form = $this->formFactory->create(RefreshTokenForm::class);
        $form->submit($content);

        if (false === $form->isValid()) {
            return $this->failJson($this->collectFormErrors($form));
        }

        /** @var RefreshTokenFormDto $data */
        $data = $form->getData();

        try {
            $refreshToken = $this->tokenService->refreshRefreshToken($data->refreshToken);
            $jwtToken = $this->tokenService->createJwtToken(
                $refreshToken->getUserIdentifier(),
                $refreshToken->getSessionId()
            );
        } catch (JwtAuthExceptionInterface $e) {
            throw new BadRequestHttpException('e6c8c8a6-c729-48f5-9aae-1df5cf810478 Invalid token');
        }

        return $this->successJson([
            TokenResponseEnum::TOKEN->value => $jwtToken->getEncodedToken(),
            TokenResponseEnum::REFRESH_TOKEN->value => $refreshToken->getEncodedToken(),
        ]);
    }

    private function collectFormErrors(FormInterface $form): array
    {
        $errors = [];
        /** @var FormError $error */
        foreach ($form->getErrors(true) as $error) {
            $origin = $error->getOrigin();
            $errors[null !== $origin ? (string) $origin->getPropertyPath() : ''] = $error->getMessage();
        }

        return $errors;
    }
}
