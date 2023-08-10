<?php

namespace App\Form;

use App\Entity\Page;
use Eckinox\TinymceBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
                        'class' => 'form-control',
                        'required' => true
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
                        'required' => true,
                        'readonly' => true
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-right'
                    ]
                ]

            )
            ->add(
                'info',
                TinymceType::class,
                [
                    'label' => 'Update Info:',
                    'required' => false,
                    'attr' => [
                        'class' => 'col',
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
                        'required' => true
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-right'
                    ]
                ]

            )
            ->add(
            'transcript',
            TinymceType::class,
                [
                    'label' => 'Transcript:',
                    'required' => false,
                    'attr' => [
                        'class' => 'col',
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-right'
                    ]
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
            'data_class' => Page::class,
        ]);
    }
}
