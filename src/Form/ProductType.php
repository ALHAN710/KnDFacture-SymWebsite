<?php

namespace App\Form;

use App\Entity\Product;
use App\Form\ApplicationType;
//use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProductType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                'type',
                ChoiceType::class,
                $this->getConfiguration("Type", "", [
                    'choices' => [
                        'PRODUCT'   => 'Product',
                        'SERVICE'   => 'Service',
                        //'LAIT'    => 'Lait'
                    ],
                ])
            )
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
            )*/;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
