<?php

namespace App\Form;

use App\Form\OrderItemType;
use App\Form\ApplicationType;
use App\Entity\CommercialSheet;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\DataTransformer\FrenchToDateTimeTransformer;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CommercialSheetType extends ApplicationType
{
    private $transformertransformer;

    public function __construct(FrenchToDateTimeTransformer $transformer)
    {
        $this->transformer = $transformer;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'orderItems',
                CollectionType::class,
                [
                    'entry_type'   => OrderItemType::class,
                    'allow_add'    => true,
                    'allow_delete' => true,
                    //'mapped'       => false
                ]
            )
            ->add(
                'itemsReduction',
                NumberType::class,
                $this->getConfiguration("Total reduction of items (%)", "Please enter the % reduction of items...", [
                    'attr' => [
                        'min'   => '0',
                        //'value' => '0'
                    ]
                ])
            )
            ->add(
                'fixReduction',
                NumberType::class,
                $this->getConfiguration("Fix Reduction (XAF)", "Please enter the fix reduction...", [
                    'attr' => [
                        'min'   => '0',
                        //'value' => '0'
                    ]
                ])
            )
            ->add(
                'deliveryFees',
                NumberType::class,
                $this->getConfiguration("Delivery Fees (XAF)", "Please enter the Delivery fees...", [
                    'attr' => [
                        'min'   => '0',
                        'value' => '0'
                    ]
                ])
            )
            ->add(
                'periodofvalidity',
                TextType::class,
                $this->getConfiguration("Due Date", "Please enter the due date of Quote...")
            )
            ->add(
                'paymentStatus',
                CheckboxType::class,
                [
                    'label'    => 'Already Paid ?',
                    'required' => false,
                ]
            )
            ->add(
                'completedStatus',
                CheckboxType::class,
                [
                    'label'    => 'Already Complete ?',
                    'required' => false,
                ]
            )
            ->add(
                'deliveryStatus',
                CheckboxType::class,
                [
                    'label'    => 'Already Deliver ?',
                    'required' => false,
                ]
            )
            /*->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary'],
            ])*/
            // ->add('number')
            // ->add('completedStatus')
            // ->add('deliveryStatus')
            // ->add('createdAt')
            // ->add('user')
        ;

        $builder->get('periodofvalidity')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommercialSheet::class,
        ]);
    }
}
