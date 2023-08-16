<?php

namespace App\Form;

use App\Entity\Cast;
use Eckinox\TinymceBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddCastType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Name:',
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
                    'label' => 'Image:',
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
                'description',
                TinymceType::class,
                [
                    'label' => 'Description:',
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
            'data_class' => Cast::class,
        ]);
    }
}
