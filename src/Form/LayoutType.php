<?php

namespace App\Form;

use App\Entity\Comic;
use App\Entity\Layout;
use App\Helpers\SettingsHelper;
use App\Traits\MediaPathTrait;
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
    use MediaPathTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var SettingsHelper $settings
         */
        $settings = $options['settings'];
        /**
         * @var Comic $comic
         */
        $comic = $options['comic'];

        $builder
            ->add(
                'showinfo',
                CheckboxType::class,
                [
                    'required' => false,
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
                    'required' => false,
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
                    'required' => false,
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
                'showcomments',
                CheckboxType::class,
                [
                    'required' => false,
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
                'icon',
                TextType::class,
                [
                    'attr' => [
                        'readonly' => true,
                        'class' => 'form-control'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-end'
                    ],
                    'label' => 'Icon:',
                    'help' => 'PNG, GIF or JPG. At most 2 MB. 512x512 suggested size'
                ]
            )
            ->add(
                'theme',
                ChoiceType::class,
                [
                    'choices' => $this->getThemes($settings, $comic->getOwner()->getUsername(), $comic->getSlug()),
                    'attr' => ['class' => 'form-select'],
                    'label' => 'Theme:',
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-end'
                    ]
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
        $resolver
            ->setRequired(['settings', 'comic'])
            ->setDefaults([
            'data_class' => Layout::class,
        ]);
    }
}
