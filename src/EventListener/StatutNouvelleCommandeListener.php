<?php

namespace App\EventListener;

use App\Entity\Commande;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\MailerInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Commande::class)]
final class StatutNouvelleCommandeListener
{
    private MailerInterface $mailer;

    // permet uniquement d'appeler la MailerInterface pour l'utiliser dans le listener par la suite
    public function __construct(MailerInterface $mailer) {
        $this->mailer = $mailer;
    }

    // cette fonction permet d'utiliser l'évènement doctrine postPersist pour envoyer un email a un client
    // uniquement quand il vient de passer une nouvelle commande
    public function postPersist(Commande $commande, PostPersistEventArgs $event): void
    {
        // permet d'envoyer le mail uniquement si la commande atteint le statut "Commande passée"
        if ($commande->getStatut() === 'Commande passée') {
            // permet de déterminer a quel utilisateur appartient la commande pour pouvoir lui envoyer le mail
            $user = $commande->getUser();

            // permet de s'assurer que l'entité dans la variable $user est bien un utilisateur avant d'envoyer le mail
            if ($user instanceof User) {
                $mail = (new TemplatedEmail())
                    ->to($user->getEmail())
                    ->from('no-reply@ViteEtGourmand.fr')
                    ->subject('Votre commande chez Vite & Gourmand')
                    ->htmlTemplate('emails/nouvelle_commande.html.twig');
                
                    $this->mailer->send($mail);
            }
        }
    }
}