<?php

namespace App\Form;

use App\Entity\BusinessContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class BusinessContactType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'socialReason',
                TextType::class,
                $this->getConfiguration("Social Reason or Name (*)", "Please enter the social reason or name...")
            )
            ->add(
                'niu',
                TextType::class,
                $this->getConfiguration("NIU", "Please enter the NIU...", [
                    'required' => false,
                ])
            )
            ->add(
                'rccm',
                TextType::class,
                $this->getConfiguration("RCCM", "Please enter the RCCM...", [
                    'required' => false,
                ])

            )
            ->add(
                'phoneNumber',
                TextType::class,
                $this->getConfiguration("Tel (*)", "Please enter phonenumber...")
            )
            ->add(
                'address',
                TextType::class,
                $this->getConfiguration("Address (*)", "Please enter address...")
            )
            ->add(
                'email',
                EmailType::class,
                $this->getConfiguration("Email Address", "Please enter email address...", [
                    'required' => false,
                ])

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
        //->add('createdAt')
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BusinessContact::class,
        ]);
    }
}
