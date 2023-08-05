<?php

namespace App\Form;

use App\Entity\Page;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Dropzone\Form\DropzoneType;

class AddPageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'Page Title:',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-right'
                    ]
                ]
            )
            ->add(
                'image',
                TextType::class,
                [
                    'label' => 'Page Image:',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-right'
                    ]
                ]

            )
            ->add(
                'info',
                TextareaType::class,
                [
                    'label' => 'Update Info:',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-right'
                    ]
                ]
            )
            ->add(
                'publishdate',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                    'label' => 'Publish Date:',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control col',
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-right'
                    ]
                ]

            )
            ->add(
            'transcript',
            TextareaType::class,
                [
                    'label' => 'Transcript:',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-right'
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
