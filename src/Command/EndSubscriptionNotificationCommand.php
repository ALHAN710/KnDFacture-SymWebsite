<?php
// src/Command/EndSubscriptionNotificationCommand.php
namespace App\Command;

use App\Entity\Enterprise;
use App\Message\UserNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EndSubscriptionNotificationCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:end-subscription-notif';
    private $manager;
    private $messageBus;

    public function __construct(EntityManagerInterface $manager, MessageBusInterface $messageBus)
    {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        $this->manager = $manager;
        $this->messageBus = $messageBus;

        parent::__construct();
    }

    protected function configure(): void
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ... put here the code to create the user
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        //$str = '====================';
        /*$output->writeln([
            $str,
            '',
        ]);*/
        // $output->writeln($str);
        // $output->write('Name | ');
        // $output->write('Remaining days | ');
        // $output->writeln('State');

        // the value returned by someMethod() can be an iterator (https://secure.php.net/iterator)
        // that generates and returns the messages with the 'yield' PHP keyword
        //$output->writeln($this->someMethod());

        // outputs a message followed by a "\n"
        // $output->writeln('Whoa!');

        // outputs a message without adding a "\n" at the end of the line
        // $output->write('You are about to ');
        // $output->write('create a user.');

        $persist = false;
        $enterprises = $this->manager->getRepository(Enterprise::class)->findAll();
        foreach ($enterprises as $enterprise) {
            $isActivated = $enterprise->getIsActivated() == true ? 1 : 0;
            // $output->writeln($str);
            // $output->write($enterprise->getSocialReason() . ' | ');
            // $output->write($enterprise->getDeadLine() . ' | ');
            // $output->writeln($isActivated);
            if ($isActivated) { //Si le compte client entreprise est actif

                if ($enterprise->getDeadLine() > 0) { //On teste si le nombre de jour d'abonnement restant est positif
                    //On teste si le nombre de jour d'abonnement restant correspond à certaines valeurs prédéfinies
                    //pour envoyer la notification
                    switch ($enterprise->getDeadLine()) {
                        case 10:
                            //Envoi de mail de notification de fin d'abonnement aux admin du compte clients et superAdmin
                            $this->addNotifToQueue($enterprise, true);
                            break;
                        case 7:
                            //Envoi de mail de notification de fin d'abonnement aux admin du compte clients et superAdmin
                            $this->addNotifToQueue($enterprise, true);
                            break;
                        case 2:
                            //Envoi de mail de notification de fin d'abonnement aux admin du compte clients et superAdmin
                            $this->addNotifToQueue($enterprise, true);
                            break;
                        case 1:
                            //Envoi de mail de notification de fin d'abonnement aux admin du compte clients et superAdmin
                            $this->addNotifToQueue($enterprise, true);
                            break;

                        default:
                            break;
                    }
                } else {
                    $enterprise->setIsActivated(false);
                    $this->manager->persist($enterprise);
                    $persist = true;

                    //Envoi de mail de notification de fin d'abonnement aux admin du compte clients et superAdmin
                    $this->addNotifToQueue($enterprise, false);
                }
            } else {

                //Envoi de mail de notification de fin d'abonnement aux admin du compte clients et superAdmin
                $this->addNotifToQueue($enterprise, false);
            }
        }

        if ($persist) { //S'il existe au moins un objet à persister
            $this->manager->flush();
        }
        // this method must return an integer number with the "exit status code"
        // of the command. You can also use these constants to make code more readable

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }

    /**
     * Permet d'ajouter la Notification à la file d'attente d'envoi de notification SMS/EMAIL
     *
     * @param Enterprise $enterprise
     * @param bool $isAlert
     * @return void
     */
    private function addNotifToQueue(Enterprise $enterprise, $isAlert = false)
    {
        $object  = '';
        $message = '';
        if ($isAlert) {
            $object  = 'Alerte FIN ABONNEMENT';
            $message = "Le " . date('d/m/Y H:i:s') . " GMT+000

Cher(e) Client(e),

Votre nom de compte " . $enterprise->getSocialReason() . " est actuellement enregistré chez KnD Factures.

Votre abonnement expire dans : " . $enterprise->subscriptionDeadLine() . ".

Pour toute information complémentaire, notre support reste à votre disposition.

Merci de votre compréhension.


L'équipe KnD Factures";
        } else {
            $object  = 'FIN ABONNEMENT';
            $message = "Le " . date('d/m/Y H:i:s') . " GMT+000

Cher(e) Client(e),

Votre nom de compte " . $enterprise->getSocialReason() . " est actuellement enregistré chez KnD Factures.

Notre système a détecté que votre abonnement a expiré, non renouvelé malgré les relances que nous avons envoyées.

Votre nom de compte a donc été désactivé.


Pour le réactiver, il vous suffit de contacter notre service client au (+237) 690442311.


IMPORTANT : En cas de non règlement sous 10 jours, votre compte pourrait être DEFINITIVEMENT effacé.

Pour toute information complémentaire, notre support reste à votre disposition.

Merci de votre compréhension.


L'équipe KnD Factures";
        }

        /*foreach ($enterprise->getUsers() as $user) {
            if ($user->getRoles()[0] === 'ROLE_ADMIN') {
                $this->messageBus->dispatch(new UserNotificationMessage($user->getId(), $message, 'Email', $object));
            }
            //$messageBus->dispatch(new UserNotificationMessage($user->getId(), $message, 'SMS', ''));
        }*/

        $adminUsers = [];
        $Users = $this->manager->getRepository('App:User')->findAll();
        foreach ($Users as $user) {
            if ($user->getRoles()[0] === 'ROLE_SUPER_ADMIN') $adminUsers[] = $user;
        }

        foreach ($adminUsers as $user) {
            $this->messageBus->dispatch(new UserNotificationMessage($user->getId(), $message, 'Email', $object));
        }
    }
}
