<?php

namespace App\Form;

//use App\Entity\User;
use App\Entity\User;
use App\Form\ApplicationType;
//use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class AccountType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstName',
                TextType::class,
                $this->getConfiguration("Prénom (*)", "Entrer le prénom...")
            )
            ->add(
                'lastName',
                TextType::class,
                $this->getConfiguration("Nom (*)", "Entrer le nom...")
            )
            ->add(
                'email',
                EmailType::class,
                $this->getConfiguration("Email (*)", "Entrer l'adresse email...")
            )
            ->add(
                'avatar',
                FileType::class,
                $this->getConfiguration(
                    "Avatar Utilisateur (IMG file)",
                    "",
                    [
                        // unmapped means that this field is not associated to any entity property
                        'mapped' => false,

                        // make it optional so you don't have to re-upload the IMG file
                        // every time you edit the Product details
                        'required' => false,

                        // unmapped fields can't define their validation using annotations
                        // in the associated entity, so you can use the PHP constraint classes
                        'constraints' => [
                            new File([
                                'maxSize' => '1024k',
                                'mimeTypes' => [
                                    'image/png',
                                    'image/jpeg',
                                ],
                                'mimeTypesMessage' => 'Please upload a valid Image format(jpeg, png)',
                            ])
                        ],
                    ]
                )
            )
            ->add(
                'countryCode',
                TextType::class,
                $this->getConfiguration("Code téléphonique :", "Ex : +237", [
                    'required' => false,
                ])

            )
            ->add(
                'phoneNumber',
                TextType::class,
                $this->getConfiguration("N° Tél (*) :", "Entrer le numéro de téléphone...")

            )
            /*->add(
                'role',
                ChoiceType::class,
                [
                    'choices' => [
                        'USER' => 'ROLE_USER',
                        'STOCK MANAGER' => 'ROLE_STOCK_MANAGER',
                        'ADMIN' => 'ROLE_ADMIN',

                    ],
                    'label'    => 'Rôle'
                ]
            )*/;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //'data_class' => User::class,
            'data_class' => User::class,
        ]);
    }
}
