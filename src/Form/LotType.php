<?php

namespace App\Form;

use App\Entity\Lot;
use App\Entity\Product;
use App\Entity\Inventory;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class LotType extends ApplicationType
{
    /*public $data = array();
    public function __construct($data)
    {
        $this->data = $data;  // Now you can use this value while creating a form field for giving any validation.
    }*/
    private $entId;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        dump($options['include_product']);
        $this->entId = $options['entId'];
        $builder
            ->add(
                'quantity',
                IntegerType::class,
                $this->getConfiguration("Quantity", "Please enter the quantity of Lot...", [
                    'attr' => [
                        'min'   => '0',
                    ]
                ])
            )
            ->add(
                'number',
                TextType::class,
                $this->getConfiguration("N° Lot", "Please enter the number of Lot...", [
                    'attr' => [
                        'min'   => '0',
                    ]
                ])
            )
            ->add(
                'duration',
                IntegerType::class,
                $this->getConfiguration("Duration (days)", "Please enter the duration in days of Lot...", [
                    'attr' => [
                        'min'   => '0',
                    ]
                ])
            )
            ->add(
                'product',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Product::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->innerJoin('p.enterprise', 'e')
                            ->where('e.id = :entId')
                            ->andWhere('p.hasStock = 1')
                            ->setParameters(array(
                                'entId'    => $this->entId,

                            ));
                        //->orderBy('u.username', 'ASC');
                    },
                    // uses the User.username property as the visible option string
                    'choice_label' => 'name',

                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            )
            ->add(
                'inventory',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Inventory::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('inv')
                            ->innerJoin('inv.enterprise', 'e')
                            ->where('e.id = :entId')
                            ->setParameters(array(
                                'entId'    => $this->entId,

                            ));
                        //->orderBy('u.username', 'ASC');
                    },
                    // uses the User.username property as the visible option string
                    'choice_label' => 'name',

                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            );
        // if ($options['include_product']) {
        //     $builder
        //         ;
        // }
        // if ($options['include_inventory']) {
        //     $builder
        //         ;
        // }
        //->add('dlc')
        //->add('createdAt')
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => Lot::class,
            'entId'             => 0,
            'include_product'   => false,
            'include_inventory' => false
        ]);
    }
}
