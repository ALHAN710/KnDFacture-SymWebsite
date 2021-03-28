<?php

namespace App\Controller;

use Symfony\Component\Mime\Email;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApplicationController extends AbstractController
{
    public function getJSONRequest($content): array
    {
        //$content = $request->getContent();
        //$content = $this->getContentAsArray($request);

        if (empty($content)) {
            throw new BadRequestHttpException("Content is empty");
            /*return $this->json([
                'code' => 400,
                'error' => "Content is empty"
            ], 400);*/
        }

        //$var = $this->json_validate($content);

        $paramJSON = json_decode($content, true); //json_decode("{\"date\":\"2020-03-20\",\"sa\":1.5}", true); 

        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
                // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
                // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
                // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }
        if (!empty($error)) {
            throw new BadRequestHttpException($error);
            /*return $this->json([
                'code' => 400,
                'error' => $error

            ], 400);*/
        }

        return  $paramJSON;
    }

    /**
     * Permet d'envoyer les emails
     *
     * @param [MailerInterface] $mailer
     * @param [string] $addressMail
     * @param  $object
     * @param [string] $mess
     * @return void
     */
    public function sendEmail($mailer, $addressMail, $object, $mess)
    {
        $email = (new Email())
            ->from('donotreply@portal-myenergyclever.com')
            ->to($addressMail)
            //->addTo('cabrelmbakam@gmail.com')
            //->cc('cabrelmbakam@gmail.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($object)
            ->text($mess);
        //->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);

        // ...
    }

    function ecart_type($donnees)
    {
        //0 - Nombre d’éléments dans le tableau
        $population = count($donnees);
        if ($population != 0) {
            //1 - somme du tableau
            $somme_tableau = array_sum($donnees);
            //2 - Calcul de la moyenne
            $moyenne = ($somme_tableau * 1.0) / $population;
            //3 - écart pour chaque valeur
            $ecart = [];
            for ($i = 0; $i < $population; $i++) {
                //écart entre la valeur et la moyenne
                $ecart_donnee = $donnees[$i] - $moyenne;
                //carré de l'écart
                $ecart_donnee_carre = bcpow($ecart_donnee, 2, 2);
                //Insertion dans le tableau
                array_push($ecart, $ecart_donnee_carre);
            }
            //4 - somme des écarts
            $somme_ecart = array_sum($ecart);
            //5 - division de la somme des écarts par la population
            $division = $somme_ecart / $population;
            //6 - racine carrée de la division
            $ecart_type = bcsqrt($division, 2);
        } else {
            $ecart_type = 0; //"Le tableau est vide";
        }
        //7 - renvoi du résultat
        return $ecart_type;
    }
}
