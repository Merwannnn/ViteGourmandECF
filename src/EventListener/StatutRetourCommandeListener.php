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
final class StatutRetourCommandeListener
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer) {
        $this->mailer = $mailer;
    }

    public function postUpdate(Commande $commande, PostUpdateEventArgs $event): void
    {
        if ($commande->getStatut() === 'En attente du retour de matériel') {
            $user = $commande->getUser();

            if ($user instanceof User) {
                $mail = (new TemplatedEmail())
                    ->to($user->getEmail())
                    ->from('no-reply@ViteEtGourmand.fr')
                    ->subject('Votre commande est terminée')
                    ->htmlTemplate('emails/commande_retour_materiel.html.twig');
                
                    $this->mailer->send($mail);
            }
        }
    }
}