<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
//use Symfony\Component\Form\AbstractType;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ProductType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entId = $options['entId'];
        //dump($options['categories']);
        if (!$options['forCategory']) {
            $builder
                ->add(
                    'name',
                    TextType::class,
                    $this->getConfiguration("Designation", "Please enter the Designation of Product/Service...")
                )
                ->add(
                    'price',
                    NumberType::class,
                    $this->getConfiguration("P.U (XAF)", "Please enter the unit price of Product/Service...", [
                        'attr' => [
                            'min' => 1
                        ]
                    ])
                )
                ->add(
                    'sku',
                    TextType::class,
                    $this->getConfiguration("SKU", "Please enter the SKU of Product/Service...", [
                        'required' => false,
                    ])
                )
                ->add(
                    'description',
                    TextareaType::class,
                    $this->getConfiguration("Description", "Please enter the description of Product/Service...", [
                        'required' => false,
                    ])
                )
                ->add(
                    'hasStock',
                    CheckboxType::class,
                    [
                        'label'    => 'En Stock ?',
                        'required' => false,
                    ]
                )
                ->add(
                    'categories',
                    CollectionType::class,
                    [
                        'entry_type'   => CategoryCollectionType::class,

                        'allow_add'    => true,
                        'allow_delete' => true,
                        'entry_options' => array(

                            'entId' => $options['entId'],
                            'categories' => $options['categories'],
                        ),
                    ]

                )
                /*->add(
                    'type',
                    ChoiceType::class,
                    $this->getConfiguration("Type", "", [
                        'choices' => [
                            'PRODUCT'   => 'Product',
                            'SERVICE'   => 'Service',
                            //'LAIT'    => 'Lait'
                        ],
                    ])
                )*/
                /*->add(
                    'instant',
                    ChoiceType::class,
                    $this->getConfiguration("Instant", "Select the instant", [
                        'choices' => [
                            'DEJEUNER' => 'Déjeuner',
                            'DINER'    => 'dîner',
                            'DESSERT'  => 'Dessert',
                            '100 ML'   => '100 mL'
                        ],
                    ])
                )
                ->add(
                    'age',
                    ChoiceType::class,
                    $this->getConfiguration("Age (Months) ", "Select the age...", [
                        'choices' => [
                            '6'   => '6',
                            '8'   => '8',
                            '12'  => '12',
                            '15'  => '15',
                            '18'  => '18',
                            '24'  => '24',
                            '36'  => '36',
                        ],
                    ])
                )
                            
                */;
        } else {
            $builder
                ->add(
                    'name',
                    TextType::class,
                    $this->getConfiguration("Designation", "Please enter the Designation of Product/Service...", [
                        'required' => false,
                    ])
                )
                ->add(
                    'price',
                    NumberType::class,
                    $this->getConfiguration("P.U (XAF)", "Please enter the unit price of Product/Service...", [
                        'required' => false,
                        'attr' => [
                            'min' => 1
                        ]
                    ])
                )
                ->add(
                    'sku',
                    TextType::class,
                    $this->getConfiguration("SKU", "Please enter the SKU of Product/Service...", [
                        'required' => false,
                    ])
                )
                /*->add(
                    'description',
                    TextareaType::class,
                    $this->getConfiguration("Description", "Please enter the description of Product/Service...", [
                        'required' => false,
                    ])
                )
                ->add(
                    'hasStock',
                    CheckboxType::class,
                    [
                        'label'    => 'En Stock ?',
                        'required' => false,
                    ]
                )*/
                ->add(
                    'productName',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => Product::class,

                        // uses the User.username property as the visible option string
                        'query_builder' => function (EntityRepository $er) use ($entId) {
                            return $er->createQueryBuilder('p')
                                //->select('c.name')
                                ->Join('p.enterprise', 'e')
                                ->where('e.id = :entId')
                                ->setParameter('entId', $entId);
                        },
                        'choice_label' => 'name',
                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                    ]
                )
                ->add(
                    'productPrice',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => Product::class,

                        // uses the User.username property as the visible option string
                        'query_builder' => function (EntityRepository $er) use ($entId) {
                            return $er->createQueryBuilder('p')
                                //->select('c.name')
                                ->Join('p.enterprise', 'e')
                                ->where('e.id = :entId')
                                ->setParameter('entId', $entId);
                        },
                        'choice_label' => 'price',
                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                    ]
                )
                ->add(
                    'productSku',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => Product::class,

                        // uses the User.username property as the visible option string
                        'query_builder' => function (EntityRepository $er) use ($entId) {
                            return $er->createQueryBuilder('p')
                                //->select('c.name')
                                ->Join('p.enterprise', 'e')
                                ->where('e.id = :entId')
                                ->setParameter('entId', $entId);
                        },
                        'choice_label' => 'sku',
                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                    ]
                )
                /*->add(
                    'productDescription',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => Product::class,

                        // uses the User.username property as the visible option string
                        'query_builder' => function (EntityRepository $er) use ($entId) {
                            return $er->createQueryBuilder('p')
                                //->select('c.name')
                                ->Join('p.enterprise', 'e')
                                ->where('e.id = :entId')
                                ->setParameter('entId', $entId);
                        },
                        'choice_label' => 'sku',
                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                    ]
                )
                ->add(
                    'productHasStock',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => Product::class,

                        // uses the User.username property as the visible option string
                        'query_builder' => function (EntityRepository $er) use ($entId) {
                            return $er->createQueryBuilder('p')
                                //->select('c.name')
                                ->Join('p.enterprise', 'e')
                                ->where('e.id = :entId')
                                ->setParameter('entId', $entId);
                        },
                        'choice_label' => 'hasStock',
                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                    ]
                )*/
                /*->add(
                    'type',
                    ChoiceType::class,
                    $this->getConfiguration("Type", "", [
                        'choices' => [
                            'PRODUCT'   => 'Product',
                            'SERVICE'   => 'Service',
                            //'LAIT'    => 'Lait'
                        ],
                    ])
                )*/
                /*->add(
                    'instant',
                    ChoiceType::class,
                    $this->getConfiguration("Instant", "Select the instant", [
                        'choices' => [
                            'DEJEUNER' => 'Déjeuner',
                            'DINER'    => 'dîner',
                            'DESSERT'  => 'Dessert',
                            '100 ML'   => '100 mL'
                        ],
                    ])
                )
                ->add(
                    'age',
                    ChoiceType::class,
                    $this->getConfiguration("Age (Months) ", "Select the age...", [
                        'choices' => [
                            '6'   => '6',
                            '8'   => '8',
                            '12'  => '12',
                            '15'  => '15',
                            '18'  => '18',
                            '24'  => '24',
                            '36'  => '36',
                        ],
                    ])
                )
                            
                */;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => Product::class,
            'entId'       => 0,
            'forCategory' => false,
            'categories'  => array()
        ]);
        $resolver->setRequired('categories'); // Requires that categories be set by the caller.
        $resolver->setAllowedTypes('categories', 'array'); // Validates the type(s) of option(s) passed.
    }
}
