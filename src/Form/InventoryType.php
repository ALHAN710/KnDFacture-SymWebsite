<?php

namespace App\Form;

use App\Entity\Inventory;
//use Symfony\Component\Form\AbstractType;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class InventoryType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                $this->getConfiguration("Name", "Please enter the name of Inventory...")
            )
            ->add(
                'approvDelay',
                NumberType::class,
                $this->getConfiguration("Supply Delay (Days)", "Please enter the supply delay...", [
                    'attr' => [
                        'min'  =>  '0'
                    ]
                ])
            )
            ->add(
                'orderingFreq',
                NumberType::class,
                $this->getConfiguration("Ordering Frequency (Days)", "Please enter the ordering frequency...", [
                    'attr' => [
                        'min'  =>  '0'
                    ]
                ])
            )
            ->add(
                'txOfService',
                ChoiceType::class,
                $this->getConfiguration("Service scale (%)", "Select the coef", [
                    'choices' => [
                        '99.9%' => '3.09',
                        '99%'   => '2.33',
                        '97%'   => '2.05',
                        '96%'   => '1.88',
                        '95%'   => '1.64',
                        '94%'   => '1.55',
                        '93%'   => '1.48',
                        '92%'   => '1.41',
                        '91%'   => '1.34',
                        '90%'   => '1.28',
                        '89%'   => '1.23',
                        '88%'   => '1.17',
                        '87%'   => '1.13',
                        '86%'   => '1.08',
                        '85%'   => '1.04',
                        '84%'   => '0.99',
                        '83%'   => '0.95',
                        '82%'   => '0.92',
                        '89%'   => '1.23',
                        '88%'   => '1.17',
                        '87%'   => '1.13',
                        '86%'   => '1.08',
                        '85%'   => '1.04',
                        '84%'   => '0.99',
                        '83%'   => '0.95',
                        '82%'   => '0.92',
                        '81%'   => '0.88',
                        '80%'   => '0.84',
                        '79%'   => '0.82',
                        '78%'   => '0.77',
                        '77%'   => '0.74',
                        '76%'   => '0.71',
                        '75%'   => '0.67',
                        '74%'   => '0.64',
                        '73%'   => '0.61',
                        '72%'   => '0.58',
                        '71%'   => '0.55',
                        '70%'   => '0.52',
                        '69%'   => '0.50',
                        '68%'   => '0.47',
                        '67%'   => '0.44',
                        '66%'   => '0.41',
                        '65%'   => '0.39',
                        '64%'   => '0.36',
                        '63%'   => '0.33',
                        '62%'   => '0.31',
                        '61%'   => '0.28',
                        '60%'   => '0.25',
                        '69%'   => '0.50',
                        '68%'   => '0.47',
                        '67%'   => '0.44',
                        '66%'   => '0.41',
                        '65%'   => '0.39',
                        '64%'   => '0.36',
                        '63%'   => '0.33',
                        '62%'   => '0.31',
                        '61%'   => '0.28',
                        '60%'   => '0.25',
                        '59%'   => '0.23',
                        '58%'   => '0.20',
                        '57%'   => '0.18',
                        '56%'   => '0.15',
                        '55%'   => '0.13',
                        '54%'   => '0.10',
                        '53%'   => '0.08',
                        '52%'   => '0.05',
                        '51%'   => '0.03',
                        '50%'   => '0.00',
                    ],
                ])
            )
            ->add(
                'managementMode',
                ChoiceType::class,
                $this->getConfiguration("Management Mode ", "Select the Management Mode...", [
                    'choices' => [
                        'LIFO'   => 'LIFO',
                        'FIFO'   => 'FIFO',
                    ],
                ])
            )
            ->add(
                'type',
                ChoiceType::class,
                $this->getConfiguration("Stock Type ", "Select the Stock Type...", [
                    'choices' => [
                        // 'MP'   => 'MP',
                        'PF'   => 'PF',
                    ],
                ])
            );
        //->add('inventoryAvailabilities');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Inventory::class,
        ]);
    }
}
