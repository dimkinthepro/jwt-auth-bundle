<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Controller;

use Dimkinthepro\JwtAuth\Infrastructure\DTO\RefreshTokenFormDto;
use Dimkinthepro\JwtAuth\Infrastructure\Enum\TokenResponseEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\InvalidTokenException;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\RequestErrorException;
use Dimkinthepro\JwtAuth\Infrastructure\Form\RefreshTokenForm;
use Dimkinthepro\JwtAuth\Infrastructure\Response\ResponseTrait;
use Dimkinthepro\JwtAuth\Infrastructure\Service\TokenService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TokenRefreshAction
{
    use ResponseTrait;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly TokenService $tokenService
    ) {
    }

    /**
     * @throws BadRequestHttpException|RequestErrorException
     */
    public function __invoke(Request $request): Response
    {
        try {
            $content = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new BadRequestHttpException('1a127ca3-3531-42eb-92fd-600fb5d06286 Invalid token');
        }

        $form = $this->formFactory->create(RefreshTokenForm::class);
        $form->submit($content);

        if (false === $form->isValid()) {
            $e = new RequestErrorException();
            $e->setFormErrors($form->getErrors(true));

            throw $e;
        }

        /** @var RefreshTokenFormDto $data */
        $data = $form->getData();

        try {
            $refreshToken = $this->tokenService->refreshRefreshToken($data->refreshToken);
            $jwtToken = $this->tokenService->createJwtToken($refreshToken->getUserIdentifier());

            return $this->successJson([
                TokenResponseEnum::TOKEN->value => $jwtToken->getEncodedToken(),
                TokenResponseEnum::REFRESH_TOKEN->value => $refreshToken->getEncodedToken(),
            ]);
        } catch (InvalidTokenException $e) {
            throw new BadRequestHttpException('e6c8c8a6-c729-48f5-9aae-1df5cf810478 Invalid token');
        }
    }
}
