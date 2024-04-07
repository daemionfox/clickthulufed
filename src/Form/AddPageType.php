<?php

namespace App\Form;

use App\Entity\Cast;
use App\Entity\Page;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Eckinox\TinymceBundle\Form\Type\TinymceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                        'class' => 'col-form-label col-3 text-end'
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
                        'class' => 'col-form-label col-3 text-end'
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
                        'class' => 'col-form-label col-3 text-end'
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
                        'class' => 'col-form-label col-3 text-end'
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
                    ]
                ]
            )
            ->addEventListener(FormEvents::POST_SET_DATA, [$this, 'postSetCastField'])
            ->addEventListener(FormEvents::POST_SET_DATA, [$this, 'postSetChapterField'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }

    public function postSetCastField(FormEvent $event): void
    {
        $form = $event->getForm();
        $page = $event->getData();
        /**
         * @var Page $page
         */
        $form->add(
                'casts',
                EntityType::class,
                [
                    'class' => 'App\Entity\Cast',
                    'choice_label' => 'getChoiceLabel',
                    'query_builder' => function (EntityRepository $entityRepository) use ($page) {
                        return $entityRepository->createQueryBuilder('cast')
                            ->andWhere('cast.Comic = :comic')
                            ->setParameter('comic', $page->getComic())
                            ->orderBy('cast.name', 'ASC');
                    },
                    'multiple' => true,
                    'expanded' => true,
                    'attr' => [
                        'class' => 'col offset-3',
                    ],
                    'choice_attr' => [
                        'class' => 'row'
                    ]
                ]
            );
    }


    public function postSetChapterField(FormEvent $event): void
    {
        $form = $event->getForm();
        $page = $event->getData();
        /**
         * @var Page $page
         */
        $form->add(
                'chapter',
                EntityType::class,
                [
                    'class' => 'App\Entity\Chapter',
                    'choice_label' => 'getChoiceLabel',
                    'query_builder' => function (EntityRepository $entityRepository) use ($page) {
                        return $entityRepository->createQueryBuilder('chapter')
                            ->andWhere('chapter.comic = :comic')
                            ->setParameter('comic', $page->getComic());
                    },
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'col form-select',
                    ],
                    'choice_attr' => [
                        'class' => 'row'
                    ]
                ]
        );
    }

}
