<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                $this->getConfiguration("Nom", "Entrez le no, de la catégorie...")
            )
            ->add(
                'products',
                CollectionType::class,
                [
                    'entry_type'   => ProductType::class,
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'entry_options' => array(

                        //'entId' => $options['entId']
                    ),
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
