<?php

namespace App\Form;

use App\Entity\Layout;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LayoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'sidebarposition',
                ChoiceType::class,
                [
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control col'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-end'
                    ],
                    'label' => "Sidebar:",
                    'choices' => [
                        "Hide Sidebar" => "",
                        "Left" => "LEFT",
                        "Right" => "RIGHT"
                    ]

                ]
            )
            ->add(
                'showinfo',
                CheckboxType::class,
                [
                    'attr' => [
                       'class' => 'checkbox-toggle d-none'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label checkbox-toggle-icon col'
                    ],
                    'label' => " ",
                ]
            )
            ->add(
                'showtranscript',
                CheckboxType::class,
                [
                    'attr' => [
                        'class' => 'checkbox-toggle d-none'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label checkbox-toggle-icon col'
                    ],
                    'label' => " ",
                ]
            )
            ->add(
                'showcast',
                CheckboxType::class,
                [
                    'attr' => [
                        'class' => 'checkbox-toggle d-none'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label checkbox-toggle-icon col'
                    ],
                    'label' => " ",
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
                    'label' => 'Header Image:',
                    'help' => 'PNG, GIF or JPG. At most 2 MB. 1600x400 maximum size'
                ]
            )
            ->add(
                'css',
                TextareaType::class,
                [
                    'attr' => [
                        'class' => 'form-control',
                        'rows' => 25
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-end'
                    ],
                    'label' => "Custom CSS:",
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
            'data_class' => Layout::class,
        ]);
    }
}
