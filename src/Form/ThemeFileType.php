<?php

namespace App\Form;

use App\Entity\Comic;
use App\Helpers\SettingsHelper;
use App\Traits\MediaPathTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class ThemeFileType extends AbstractType
{
    use MediaPathTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'filename',
                HiddenType::class,
            )
            ->add(
                'theme',
                HiddenType::class,
            )
            ->add(
                'data',
                TextareaType::class,
                [
                    'attr' => [
                        'required' =>true,
                        'class' => 'form-control mb-3',
                        'rows' => 30,
                    ],
                    'label' => "File Contents:",
                    'label_attr' => [
                        'class' => 'fs-3'
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