<?php

namespace App\Form;

use App\Entity\Comic;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditComicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Comic Name:',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control',
                        'readonly' => true
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-right'
                    ]
                ]

            )
            ->add(
                'slug',
                TextType::class,
                [
                    'label' => 'Unique Identifier:',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control',
                        'readonly' => true
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-right'
                    ]
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'label' => 'Description:',

                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-right'
                    ]
                ]
            )
            ->add(
                'schedule',
                ScheduleType::class

            )
//            ->add(
//                'admin',
//                CollectionType::class,
//                [
//                    'label' => 'Administrators:',
//                    'entry_type' => UserType::class,
//                    'attr' => [
//                        'class' => 'form-control'
//                    ],
//                    'label_attr' => [
//                        'class' => 'col-form-label col-3 text-end'
//                    ]
//                ]
//            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comic::class,
        ]);
    }
}
