<?php

namespace App\EventListener;

use App\Entity\Commande;
use App\Entity\CommandeStatutHistorique;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class CommandeStatutHistoriqueListener
{
    public function __construct(private EntityManagerInterface $entityManager) {
    }

    public function postUpdate(PostUpdateEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$entity instanceof Commande) {
            return;
        }

        $changeSet = $this->entityManager->getUnitOfWork()->getEntityChangeSet($entity);

        if (!isset($changeSet['statut'])) {
            return;
        }

        $statutSuivant = $changeSet['statut'][1];

        $statutHistorique = new CommandeStatutHistorique();
        $statutHistorique->setCommande($entity);
        $statutHistorique->setStatutSuivant($statutSuivant);
        $statutHistorique->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($statutHistorique);
        $this->entityManager->flush();
    }
}
