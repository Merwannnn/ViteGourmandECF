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

    public function __construct(MailerInterface $mailer) {
        $this->mailer = $mailer;
    }

    public function postPersist(Commande $commande, PostPersistEventArgs $event): void
    {
        if ($commande->getStatut() === 'Commande passée') {
            $user = $commande->getUser();

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