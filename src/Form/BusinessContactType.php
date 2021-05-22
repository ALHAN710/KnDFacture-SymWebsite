<?php

namespace App\Form;

use App\Entity\BusinessContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class BusinessContactType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'phoneNumber',
                TextType::class,
                $this->getConfiguration("Tél (*)", "Entrer le numéro de téléphone...")
            )
            ->add(
                'address',
                TextType::class,
                $this->getConfiguration("Adresse", "Entrer l'adresse...")
            )
            ->add(
                'email',
                EmailType::class,
                $this->getConfiguration("Adresse Email", "Entrer l'adresse email svp...", [
                    'required' => false,
                ])

            )
            ->add(
                'moreInfos',
                TextareaType::class,
                $this->getConfiguration("Plus d'informations", "Plus d'informations...", ['required' => false])
            )
            /*->add(
                'deliveryAddress',
                CollectionType::class,
                [
                    'entry_type'   => DeliveryAddressType::class,
                    'allow_add'    => true,
                    'allow_delete' => true
                ]
            )*/;
        if ($options['type'] == 'supplier') {
            $builder
                ->add(
                    'niu',
                    TextType::class,
                    $this->getConfiguration("NIU", "Entrer le numéro d'identification unique...", [
                        'required' => false,
                    ])
                )
                ->add(
                    'rccm',
                    TextType::class,
                    $this->getConfiguration("RCCM", "Entrer le numéro du registre du commerce...", [
                        'required' => false,
                    ])

                )
                ->add(
                    'socialReason',
                    TextType::class,
                    $this->getConfiguration("Raison Sociale ou Nom (*)", "Entrer la raison sociale...")
                );
        } else {
            $builder->add(
                'socialReason',
                TextType::class,
                $this->getConfiguration("Raison Sociale ou Nom (*)", "Entrer le nom et le prénom...")
            );
        }
        //->add('createdAt')
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BusinessContact::class,
            'type'  => 'customer'
        ]);
    }
}
