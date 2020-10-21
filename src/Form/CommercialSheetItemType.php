<?php

namespace App\Form;

use App\Entity\Product;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
use App\Entity\CommercialSheetItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class CommercialSheetItemType extends ApplicationType
{
    private $entId;
    private $isEdit;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->entId  = $options['entId'];
        $this->isEdit = $options['isEdit'];
        //dump($this->isEdit);
        $builder
            ->add(
                'reference',
                TextType::class,
                $this->getConfiguration("Reference", "Please enter the reference of Offer...", [
                    'required' => false,
                    'attr' => [
                        //'disabled' => true,
                        'readonly' => $this->isEdit
                    ]

                ])
            )
            ->add(
                'designation',
                TextType::class,
                $this->getConfiguration("Designation", "Please enter the designation of Offer...", [
                    'attr' => [
                        'readonly' => $this->isEdit
                    ]
                ])
            )
            ->add(
                'pu',
                NumberType::class,
                $this->getConfiguration("P.U (XAF)", "Please enter the unit price of service", [
                    'attr' => [
                        'min' => 0,
                        'readonly' => $this->isEdit
                    ]
                ])
            )
            ->add(
                'remise',
                NumberType::class,
                $this->getConfiguration("Remise (%)", "Please enter the % remise of item", [
                    'attr' => [
                        'min' => 0,

                    ]
                ])
            )
            ->add(
                'quantity',
                IntegerType::class,
                $this->getConfiguration("Qty", "Please enter the quantity of Offer...", [
                    'attr' => [
                        'min'   => '0',
                    ]
                ])
            )
            ->add(
                'productSku',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Product::class,

                    // uses the User.username property as the visible option string
                    'choice_label' => 'sku',
                    //'choices' => $options['orderItem'],
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->innerJoin('p.enterprise', 'e')
                            ->where('e.id = :entId')
                            ->setParameters(array(
                                'entId'    => $this->entId,
                            ));
                    },
                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            )
            ->add(
                'product',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Product::class,

                    // uses the User.username property as the visible option string
                    'choice_label' => 'name',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->innerJoin('p.enterprise', 'e')
                            ->where('e.id = :entId')
                            ->setParameters(array(
                                'entId'    => $this->entId,
                            ));
                    },

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
                    'choice_label' => 'price',
                    //'choices' => $options['orderItem'],
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->innerJoin('p.enterprise', 'e')
                            ->where('e.id = :entId')
                            ->setParameters(array(
                                'entId'    => $this->entId,
                            ));
                    },
                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            )
            ->add(
                'available',
                NumberType::class,
                $this->getConfiguration("Available", "Please enter the availability of product in stock", [
                    'attr' => [
                        //'disabled' => true,
                        'readonly' => true
                    ]
                ])
            )
            ->add(
                'productType',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Product::class,

                    // uses the User.username property as the visible option string
                    'choice_label'  => 'hasStock',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->innerJoin('p.enterprise', 'e')
                            ->where('e.id = :entId')
                            ->setParameters(array(
                                'entId'    => $this->entId,

                            ));
                    },
                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            )
            ->add(
                'itemOfferType',
                TextType::class
            )
            ->add(
                'amountBrutHT',
                NumberType::class,
                $this->getConfiguration("Amount", "", [
                    'attr' => [
                        'min'      => '0',
                        //'value'    => '0',
                        'readonly' => true,
                    ]
                ])
            )
            ->add(
                'amountNetHT',
                NumberType::class,
                $this->getConfiguration("Amount", "", [
                    'attr' => [
                        'min'      => '0',
                        //'value'    => '0',
                        'readonly' => true,
                    ]
                ])
            )

            //->add('commercialSheet')
        ;

        if ($this->isEdit) {
            $builder
                ->add(
                    'isChanged',
                    IntegerType::class,
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommercialSheetItem::class,
            'entId'      => 0,
            'isEdit'     => false,
        ]);
    }
}
