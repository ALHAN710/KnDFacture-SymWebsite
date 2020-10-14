<?php

namespace App\DataFixtures;

use App\Entity\Enterprise;
use Faker;
use DateTime;
use App\Entity\Role;
use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    //Constructeur pour utiliser la fonction d'encodage de mot passe
    //encodePassword($entity, $password)
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');
        $faker->seed(1337);
        //$slugify = new Slugify();
        $nb = 0;
        $genders = ['male', 'female'];
        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);
        $superAdminRole = new Role();
        $superAdminRole->setTitle('ROLE_SUPER_ADMIN');
        $manager->persist($superAdminRole);

        $abonnementName = ['KnD Bill Basic', 'KnD Bill Premium', 'KnD Bill & Stock Basic', 'KnD Bill & Stock Premium One'];
        $sheetNb = [200, 19022018, 200, 19022018];
        $refNb  = [0, 0, 50, 50];
        $tarifs = [
            '{"1": 4000,"6": 24000,"12": 45000}',
            '{"1": 99000,"6": 59400,"12": 110000}', '{"1": 12400,"6": 74400,"12": 140000}',
            '{"1": 16000,"6": 96000,"12": 190000}',
        ];
        $subs = [];
        foreach ($tarifs as $key => $value) {
            $sub = new Subscription();
            $sub->setName($abonnementName[$key])
                ->setTarifs(json_decode($value))
                ->setSheetNumber($sheetNb[$key])
                ->setProductRefNumber($refNb[$key]);

            $subs[] = $sub;
            $manager->persist($sub);
        }

        $enterprise = new Enterprise();
        $enterprise->setSocialReason('LEELOU BABY FOOD SAS')
            ->setNiu('M 042014440616 M')
            ->setRccm('RC/DCN/2020/13/770')
            ->setAddress('Bépanda Camtel, BP : 2702 Douala')
            ->setPhoneNumber('+237 694342007')
            ->setTva(19.25)
            ->setEmail('contact@leeloubabyfood.com')
            ->setSubscription($subs[2])
            ->setSubscriptionDuration(1);
        $manager->persist($enterprise);

        //$date = new DateTime(date('Y-m-d H:i:s'));
        $adminUser = new User();
        $adminUser->setEmail('alhadoumpascal@gmail.com')
            ->setFirstName('Pascal')
            ->setLastName('ALHADOUM')
            ->setHash($this->encoder->encodePassword($adminUser, 'password'))
            ->addUserRole($superAdminRole)
            ->setPhoneNumber('690442311');

        $manager->persist($adminUser);

        $adminUser = new User();
        $adminUser->setEmail('cabrelmbakam@gmail.com')
            ->setFirstName('Cabrel')
            ->setLastName('MBAKAM')
            ->setHash($this->encoder->encodePassword($adminUser, 'password'))
            ->addUserRole($superAdminRole)
            ->setPhoneNumber('690304593');

        $manager->persist($adminUser);

        $adminUser = new User();
        $adminUser->setEmail('naomidinamona@gmail.com')
            ->setFirstName('Naomi')
            ->setLastName('DINAMONA')
            ->setHash($this->encoder->encodePassword($adminUser, 'password'))
            ->addUserRole($adminRole)
            ->setEnterprise($enterprise)
            ->setPhoneNumber('654289625');

        $manager->persist($adminUser);


        $manager->flush();
    }
}
