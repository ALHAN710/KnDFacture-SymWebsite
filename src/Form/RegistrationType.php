<?php

namespace App\Form;

use App\Entity\User;
//use Symfony\Component\Form\AbstractType;
use App\Entity\Enterprise;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstName',
                TextType::class,
                $this->getConfiguration("First Name (*)", "Please enter your first Name...")
            )
            ->add(
                'lastName',
                TextType::class,
                $this->getConfiguration("Nom (*)", "Please enter your last Name...")
            )
            ->add(
                'email',
                EmailType::class,
                $this->getConfiguration("Email (*)", "Please enter your email here...")
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
                $this->getConfiguration("Country code :", "Telephone code of your country", [
                    'required' => false,
                ])

            )
            ->add(
                'phoneNumber',
                TextType::class,
                $this->getConfiguration("N° Tel (*) :", "Your Phone Number please...")

            );
        // ->add('phoneNumber')
        // ->add('countryCode')
        // ->add('verificationCode')
        // ->add('verified')
        // ->add('createdAt')
        // ->add('userRoles')
        if (!$options['isEdit']) {
            $builder
                ->add(
                    'hash',
                    PasswordType::class,
                    $this->getConfiguration("Password (*)", "Please enter your password...")
                )
                ->add(
                    'passwordConfirm',
                    PasswordType::class,
                    $this->getConfiguration("Confirmation de mot de passe (*)", "Veuillez confirmer votre mot de passe")
                );
        }
        if ($options['isSupAdmin']) {
            $builder->add(
                'enterprise',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Enterprise::class,
                    /*'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->innerJoin('p.enterprise', 'e')
                            ->where('e.id = :entId')
                            ->andWhere('p.hasStock = 1')
                            ->setParameters(array(
                                'entId'    => $this->entId,

                            ));
                        //->orderBy('u.username', 'ASC');
                    },*/
                    // uses the User.username property as the visible option string
                    'choice_label' => 'socialReason',

                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            )
                ->add(
                    'role',
                    ChoiceType::class,
                    [
                        'choices' => [
                            'FACTURIER'             => 'ROLE_USER',
                            'SELLER'                => 'ROLE_SELLER',
                            'GESTIONNAIRE DE STOCK' => 'ROLE_STOCK_MANAGER',
                            'ADMINISTRATEUR'        => 'ROLE_ADMIN',

                        ],
                        'label'    => 'Attribut'
                    ]
                );
        } else {
            $builder->add(
                'role',
                ChoiceType::class,
                [
                    'choices' => [
                        'FACTURIER'             => 'ROLE_USER',
                        'GESTIONNAIRE DE STOCK' => 'ROLE_STOCK_MANAGER',
                        'ADMINISTRATEUR'        => 'ROLE_ADMIN',

                    ],
                    'label'    => 'Attribut'
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'isSupAdmin' => false,
            'isEdit'     => false,
        ]);
    }
}
