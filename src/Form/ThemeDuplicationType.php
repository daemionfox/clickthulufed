<?php

namespace App\Form;

use App\Entity\Comic;
use App\Helpers\SettingsHelper;
use App\Traits\MediaPathTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeDuplicationType extends AbstractType
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
                'targetname',
                TextType::class,
                [
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'label' => 'New Theme Name:',
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-end'
                    ]
                ]
            )
            ->add(
                'sourcetheme',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices' => $this->getThemes($settings, $comic->getOwner()->getUsername(), $comic->getSlug()),
                    'attr' => [
                        'class' => 'form-select'
                    ],
                    'label' => 'Source Theme:',

                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-end'
                    ]
                ]
            )
            ->add(
                'targettheme',
                TextType::class,
                [
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control',
                        'data-comic' => $comic->getSlug()
                    ],
                    'label' => 'Target Theme:',
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-end'
                    ]
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'attr' => [
                        'class' => 'btn btn-success btn-block',
                    ],
                    'disabled' => true
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['settings', 'comic'])
            ->setDefaults([
            // Configure your form options here
        ]);
    }
}
