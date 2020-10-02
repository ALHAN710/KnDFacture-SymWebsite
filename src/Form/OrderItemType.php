<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\OrderItem;
//use Symfony\Component\Form\AbstractType;
use App\Form\ProductType;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class OrderItemType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /*->add(
                'sku',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Product::class,

                    // uses the User.username property as the visible option string
                    'choice_label' => 'sku',
                    //'choices' => $options['orderItem'],

                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            )*/
            ->add(
                'product',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Product::class,

                    // uses the User.username property as the visible option string
                    'choice_label' => 'name',

                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            )
            ->add(
                'offerType',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Product::class,

                    // uses the User.username property as the visible option string
                    'choice_label' => 'type',

                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            )
            ->add(
                'offerIn',
                TextType::class,
                $this->getConfiguration("Service/Product", "Please enter the Service Designation...", [
                    'required' => false,

                ])
            )
            ->add(
                'price',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Product::class,
                    'choice_label' => 'price',
                    //'multiple' => true,
                    //'expanded' => true,
                    'attr' => [
                        //'disabled' => true,
                        'readonly' => true
                    ]
                ]
            )
            ->add(
                'priceIn',
                NumberType::class,
                $this->getConfiguration("P.U (XAF)", "Please enter the unit price of service", [
                    'required' => false,
                ])
            )
            ->add(
                'priceView',
                NumberType::class,
                $this->getConfiguration("P.U (XAF)", "Please enter the unit price of service", [
                    'attr' => [
                        'readonly' => true
                    ]
                ])
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
                'quantity',
                NumberType::class,
                $this->getConfiguration("Qty", "Please enter the quantity of product...", [
                    'attr' => [
                        'min'   => '0',
                    ]
                ])
            )
            ->add(
                'amount',
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
                'offerTypeIn',
                TextType::class,
                $this->getConfiguration("Offer Type", "Please enter the type of Offer...", [
                    'required' => false,

                ])
            );
        //->add('order_');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderItem::class,
            //'orderItem' => array()
        ]);
        //$resolver->setAllowedTypes('orderItem', 'Product');
    }
}
