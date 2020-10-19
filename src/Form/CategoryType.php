<?php

namespace App\Form;

use App\Entity\Category;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CategoryType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                $this->getConfiguration("Nom", "Veuillez entrer le nom de la catégorie svp...")
            )
            //->add('products')
            ->add(
                'products',
                CollectionType::class,
                [
                    'entry_type'   => ProductType::class,

                    'allow_add'    => true,
                    'allow_delete' => true,
                    'entry_options' => array(

                        'entId'       => $options['entId'],
                        'forCategory' => $options['forCategory'],
                    ),
                ]

            )
            //->add('entreprise')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => Category::class,
            'entId'       => 0,
            'forCategory' => true,
        ]);
    }
}
