<?php

namespace App\Form;

use App\Entity\Comic;
use App\Entity\Tag;
use App\Entity\User;
use App\Enumerations\NavigationTypeEnumeration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                        'class' => 'col-form-label col-3 text-end'
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
                        'class' => 'col-form-label col-3 text-end'
                    ]
                ]
            )
            ->add(
                'navigationtype',
                ChoiceType::class,
                [
                    'choices' => NavigationTypeEnumeration::getChoices(),
                    'label' => 'Nav Type:',
                    'label_attr' => [
                        'class' => 'col-3 col-form-label text-end'
                    ],
                    'attr' => [
                        'class' => 'form-select'
                    ],
                    'required' => true,
                    'multiple' => false,
                ]
            )
            ->add(
                'schedule',
                ScheduleType::class

            )
            ->addEventListener(FormEvents::POST_SET_DATA,
                [$this, 'postSetTagsField']
            )
            ->addEventListener(
                FormEvents::POST_SET_DATA,
                [$this, 'postSetSlugField']
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

    public function preSubmitTags(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();
        $tags = explode(",", $data['tags']);
        $tags = array_map("trim", $tags);
        $data['tags'] = $tags;
        $event->setData($data);
    }

    public function postSetTagsField(FormEvent $event): void
    {
        $form = $event->getForm();
        /**
         * @var Comic $data
         */
        $data = $event->getData();
        $tags = $data->getTags();
        $taglist = [];
        /**
         * @var Tag $tag
         */
        foreach ($tags as $tag) {
            $taglist[] = $tag->getTag();
        }

        $tagdata = join(", ", $taglist);

        $form->add(
            'tags',
            TextType::class,
            [
                'label' => 'Tags:',
                'label_attr' => [
                    'class' => 'col-3 col-form-label text-end'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'mapped' => false,
                'data' => $tagdata
            ]
        );
    }
    public function postSetSlugField(FormEvent $event): void
    {
        $form = $event->getForm();
        /**
         * @var Comic $data
         */
        $data = $event->getData();
        $readonly = !empty($data->getId());
        $form->add(
        'slug',
        TextType::class,
        [
            'label' => 'Unique Identifier:',
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'readonly' => $readonly
            ],
            'label_attr' => [
                'class' => 'col-form-label col-3 text-end'
            ]
        ]
    );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comic::class,
        ]);
    }
}
