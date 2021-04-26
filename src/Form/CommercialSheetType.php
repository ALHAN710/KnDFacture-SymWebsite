<?php

namespace App\Form;

use App\Entity\BusinessContact;
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
    private $entId;
    private $hasBC;
    private $type;
    private $btype = 'customer';

    public function __construct() //FrenchToDateTimeTransformer $transformer
    {
        //$this->transformer = $transformer;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->entId  = $options['entId'];
        $this->hasBC = $options['hasBC'];
        $this->type  = $options['type'];

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
                'shippingFees',
                NumberType::class,
                $this->getConfiguration("Frais de Livraison (XAF)", "Veuillez spécifier le montant de livraison...", [
                    'attr' => [
                        'min'   => '0',
                        //'value' => '0'
                    ]
                ])
            )
            ->add(
                'fixReduction',
                NumberType::class,
                $this->getConfiguration("Réductions Fixes (XAF)", "Veuillez spécifier le montant des réductions fixes...", [
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
                    'label'    => 'Soldé ?',
                    'required' => false,
                ]
            )
            ->add(
                'completedStatus',
                CheckboxType::class,
                [
                    'label'    => 'Soldé et Livré ?',
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

        if (!$this->hasBC) {
            if ($this->type === 'purchaseorder') {
                $this->btype = 'supplier';

                $builder
                    ->add(
                        'niu',
                        EntityType::class,
                        [
                            // looks for choices from this entity
                            'class' => BusinessContact::class,

                            // uses the User.username property as the visible option string
                            'choice_label' => 'niu',
                            'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('b')
                                    ->innerJoin('b.enterprises', 'e')
                                    ->where('b.type = :btype')
                                    ->andWhere('e.id = :entId')
                                    ->orderBy('b.socialReason', 'ASC')
                                    ->setParameters(array(
                                        'entId'   => $this->entId,
                                        'btype'   => $this->btype,
                                    ));
                            },

                            // used to render a select box, check boxes or radios
                            // 'multiple' => true,
                            // 'expanded' => true,
                        ]
                    )
                    ->add(
                        'rccm',
                        EntityType::class,
                        [
                            // looks for choices from this entity
                            'class' => BusinessContact::class,

                            // uses the User.username property as the visible option string
                            'choice_label' => 'rccm',
                            'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('b')
                                    ->innerJoin('b.enterprises', 'e')
                                    ->where('b.type = :btype')
                                    ->andWhere('e.id = :entId')
                                    ->orderBy('b.socialReason', 'ASC')
                                    ->setParameters(array(
                                        'entId'   => $this->entId,
                                        'btype'   => $this->btype,
                                    ));
                            },

                            // used to render a select box, check boxes or radios
                            // 'multiple' => true,
                            // 'expanded' => true,
                        ]
                    );
            }
            $builder
                ->add(
                    'socialReason',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => BusinessContact::class,

                        // uses the User.username property as the visible option string
                        'choice_label' => 'socialReason',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('b')
                                ->innerJoin('b.enterprises', 'e')
                                ->where('b.type = :btype')
                                ->andWhere('e.id = :entId')
                                ->orderBy('b.socialReason', 'ASC')
                                ->setParameters(array(
                                    'entId'   => $this->entId,
                                    'btype'   => $this->btype,
                                ));
                        },

                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                    ]
                )
                ->add(
                    'tel',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => BusinessContact::class,

                        // uses the User.username property as the visible option string
                        'choice_label' => 'phoneNumber',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('b')
                                ->innerJoin('b.enterprises', 'e')
                                ->where('b.type = :btype')
                                ->andWhere('e.id = :entId')
                                ->orderBy('b.socialReason', 'ASC')
                                ->setParameters(array(
                                    'entId'   => $this->entId,
                                    'btype'   => $this->btype,
                                ));
                        },

                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                    ]
                )
                ->add(
                    'address',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => BusinessContact::class,

                        // uses the User.username property as the visible option string
                        'choice_label' => 'address',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('b')
                                ->innerJoin('b.enterprises', 'e')
                                ->where('b.type = :btype')
                                ->andWhere('e.id = :entId')
                                ->orderBy('b.socialReason', 'ASC')
                                ->setParameters(array(
                                    'entId'   => $this->entId,
                                    'btype'   => $this->btype,
                                ));
                        },

                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                    ]
                );
        }
        //$builder->get('periodofvalidity')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommercialSheet::class,
            'entId'      => 0,
            'isEdit'     => false,
            'hasBC'      => false,
            'type'       => 'bill',
        ]);
    }
}
