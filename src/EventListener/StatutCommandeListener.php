<?php

namespace App\EventListener;

use App\Entity\Commande;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\MailerInterface;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Commande::class)]
final class StatutCommandeListener
{
    private MailerInterface $mailer;

    // permet uniquement d'appeler la MailerInterface pour l'utiliser dans le listener par la suite
    public function __construct(MailerInterface $mailer) {
        $this->mailer = $mailer;
    }

    // cette fonction permet d'utiliser l'évènement doctrine postUpdate pour envoyer un email a un client
    // uniquement quand sa commande atteint un certain statut
    public function postUpdate(Commande $commande, PostUpdateEventArgs $event): void
    {
        // permet d'envoyer le mail uniquement si la commande atteint le statut "Terminée"
        if ($commande->getStatut() === 'Terminée') {
            // permet de déterminer a quel utilisateur appartient la commande pour pouvoir lui envoyer le mail
            $user = $commande->getUser();

            // permet de s'aasurer que l'entité dans la variable $user est bien un utilisateur avant d'envoyer le mail
            if ($user instanceof User) {
                $mail = (new TemplatedEmail())
                    ->to($user->getEmail())
                    ->from('no-reply@ViteEtGourmand.fr')
                    ->subject('Votre commande est terminée')
                    ->htmlTemplate('emails/commande_terminée.html.twig');
                
                    $this->mailer->send($mail);
            }
        }
    }
}
