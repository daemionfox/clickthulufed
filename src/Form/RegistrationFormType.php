<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'Username:',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control col'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-right'
                    ]
                ]
            )
            ->add('email',
                EmailType::class, [
                    'label' => 'Email Address:',
                    'required' => true,
                    'attr' => [
                        'class' => 'col form-control'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-right'
                    ]
                ]
            )
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'label' => 'Password:',
                'type' => PasswordType::class,
                'invalid_message' => 'Password fields must match.',
                'attr' => [
                   'class' => ''
                ],
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'col form-control'
                    ],
                ],
                'mapped' => false,
                'label_attr' => [
                    'class' => 'col-form-label col-3 text-right'
                ],
                'required' => true,
                'first_options'=> ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
