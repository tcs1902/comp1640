<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'constraints' => [
                    new UserPassword(),
                ],
            ])
            ->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'New Password and Confirm New Password must match.',
            'required' => true,
            'first_options' => ['label' => 'New Password'],
            'second_options' => ['label' => 'Confirm New Password'],
        ]);
    }
}
