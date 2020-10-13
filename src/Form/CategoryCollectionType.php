<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CategoryCollectionType extends ApplicationType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entId = $options['entId'];
        //dump($options['categories']);
        $builder
            ->add(
                'category',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Category::class,

                    // uses the User.username property as the visible option string
                    'query_builder' => function (EntityRepository $er) use ($entId) {
                        return $er->createQueryBuilder('c')
                            //->select('c.name')
                            ->Join('c.entreprise', 'e')
                            ->where('e.id = :entId')
                            ->setParameter('entId', $entId);
                    },
                    'choice_label' => 'name',
                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            );
        /*
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
            )
            */
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            'entId'      => null,
            'categories' => array()
        ]);
        $resolver->setRequired('categories'); // Requires that categories be set by the caller.
        $resolver->setAllowedTypes('categories', 'array'); // Validates the type(s) of option(s) passed.
    }
}
