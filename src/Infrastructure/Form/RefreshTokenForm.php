<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Form;

use Dimkinthepro\JwtAuth\Infrastructure\DTO\RefreshTokenFormDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class RefreshTokenForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('refreshToken', TextType::class, [
            'required' => true,
            'constraints' => [
                new Constraints\NotBlank(),
                new Constraints\Length(['max' => 10000]),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RefreshTokenFormDto::class,
            'csrf_protection' => false,
        ]);
    }
}
