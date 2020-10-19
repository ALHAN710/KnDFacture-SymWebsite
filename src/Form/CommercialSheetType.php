<?php

namespace App\Form;

use App\Form\ApplicationType;
use App\Entity\CommercialSheet;
use Doctrine\ORM\EntityRepository;
use App\Form\CommercialSheetItemType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\DataTransformer\FrenchToDateTimeTransformer;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CommercialSheetType extends ApplicationType
{
    //private $transformer;

    public function __construct() //FrenchToDateTimeTransformer $transformer
    {
        //$this->transformer = $transformer;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'commercialSheetItems',
                CollectionType::class,
                [
                    'entry_type'    => CommercialSheetItemType::class,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'entry_options' => array(
                        'isEdit' => $options['isEdit'],
                        'entId'  => $options['entId']
                    ),
                ]
            )
            ->add(
                'itemsReduction',
                NumberType::class,
                $this->getConfiguration("Reduction Totale sur les Articles (%)", "Veuillez spécifier la réducction totale sur les articles...", [
                    'attr' => [
                        'min'   => '0',
                        //'value' => '0'
                    ]
                ])
            )
            ->add(
                'fixReduction',
                NumberType::class,
                $this->getConfiguration("Reductions Fixes (XAF)", "Veuillez spécifier le montant des réductions fixes...", [
                    'attr' => [
                        'min'   => '0',
                        //'value' => '0'
                    ]
                ])
            )
            ->add(
                'deliveryMode',
                TextType::class,
                $this->getConfiguration("Mode de Livraison", "Veuillez spécifier le mode de livraison...", [
                    'required' => false,
                ])
            )
            ->add(
                'paymentMode',
                TextType::class,
                $this->getConfiguration("Mode de Paiement", "Veuillez spécifier le mode de paiement...", [
                    'required' => false,
                ])
            )
            ->add(
                'duration',
                IntegerType::class,
                $this->getConfiguration("Durée (jours)", "Veuillez spécifier la durée de validité du devis...", [
                    'attr' => [
                        'min'   => '0',
                    ]
                ])
            )
            ->add(
                'paymentStatus',
                CheckboxType::class,
                [
                    'label'    => 'Payé ?',
                    'required' => false,
                ]
            )
            ->add(
                'completedStatus',
                CheckboxType::class,
                [
                    'label'    => 'Payé et Livré ?',
                    'required' => false,
                ]
            )
            ->add(
                'deliveryStatus',
                CheckboxType::class,
                [
                    'label'    => 'Livré ?',
                    'required' => false,
                ]
            )
            ->add(
                'advancePayment',
                NumberType::class,
                $this->getConfiguration("Avance (XAF)", "Veuillez spécifier le montant avancé...", [
                    'attr' => [
                        'min'   => '0',
                        //'value' => '0'
                    ]
                ])
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

        //$builder->get('periodofvalidity')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommercialSheet::class,
            'entId'      => 0,
            'isEdit'     => false,
        ]);
    }
}
