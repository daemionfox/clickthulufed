<?php

namespace App\Form;

use App\Entity\Schedule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduleType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {



        $builder
            ->add(
                'sunday',
                CheckboxType::class,
                $this->options('Sun')
            )
            ->add(
                'monday',
                CheckboxType::class,
                $this->options('Mon')
            )
            ->add(
                'tuesday',
                CheckboxType::class,
                $this->options('Tue')
            )
            ->add(
                'wednesday',
                CheckboxType::class,
                $this->options('Wed')
            )
            ->add(
                'thursday',
                CheckboxType::class,
                $this->options('Thu')
            )
            ->add(
                'friday',
                CheckboxType::class,
                $this->options('Fri')
            )
            ->add(
                'saturday',
                CheckboxType::class,
                $this->options('Sat')
            )
            ->add(
                'time',
                TimeType::class,
                [
                    'label' => 'Time:',
                    'input_format' => 'H:i',
                    'widget' => 'choice',
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-end'
                    ],
                    'attr' => [

                    ]
                ]
            )
            ->add(
                'timezone',
                TimezoneType::class,
                [
                    'preferred_choices' => [
                        'America/New_York'
                    ],
                    'label' => 'Timezone:',
                    'label_attr' => [
                        'class' => 'col-form-label col-3 text-end'
                    ],
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
        ;


    }

    protected function options(string $label): array
    {
        return [
            'label' => $label,
            'label_attr' => [
                'class' => 'col-form-label schedule-icon col-1 border text-center'
            ],
            'attr' => [
                'class' => 'd-none schedule-checkbox'
            ],
            'required' => false
        ];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Schedule::class,
        ]);
    }

}