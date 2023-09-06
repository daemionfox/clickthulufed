<?php

namespace App\Form;

use App\Entity\User;
use Eckinox\TinymceBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'image',
                TextType::class,
                [
                    'attr' => [
                        'readonly' => true,
                        'class' => 'form-control'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-end'
                    ],
                    'label' => 'User Icon:',
                    'help' => 'PNG, GIF or JPG. At most 2 MB. Suggested size 512x512 px'
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-end'
                    ],
                    'label' => 'Name:',
                ]
            )
            ->add(
                'biography',
                TinymceType::class,
                [
                    'label' => 'Biography:',
                    'required' => false,
                    'attr' => [
                        'class' => 'col',
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-end'
                    ]
                ]
            )
            ->add(
                'headerimage',
                TextType::class,
                [
                    'attr' => [
                        'readonly' => true,
                        'class' => 'form-control'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-end'
                    ],
                    'label' => 'Banner:',
                    'help' => 'PNG, GIF or JPG. At most 2 MB. 1600x400 maximum size'
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'attr' => [
                        'class' => 'btn btn-success btn-block',
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
