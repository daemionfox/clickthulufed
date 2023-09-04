<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class InviteUsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'users',
                TextareaType::class,
                [
                    'attr' => [
                        'required' =>true,
                        'class' => 'form-control mb-3',
                        'rows' => 30,
                    ],
                    'label' => "Email Addresses:",
                    'label_attr' => [
                        'class' => 'fw-bold'
                    ]
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'attr' => [
                        'class' => 'btn btn-success',
                    ],
                    'label' => 'Save File'
                ]
            )
        ;
    }
}