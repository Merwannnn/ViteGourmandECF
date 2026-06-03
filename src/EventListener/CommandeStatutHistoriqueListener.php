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
    // permet uniquement d'appeler l'entity manager pour l'utiliser dans le listener par la suite
    public function __construct(private EntityManagerInterface $entityManager) {
    }

    // cette fonction permet d'utiliser l'évènement doctrine postUpdate pour enregistrer les nouveau statut d'une commande
    // dans une autre entité pour pouvoir avoir un historique des statut d'une commande
    public function postUpdate(PostUpdateEventArgs $event): void
    {
        // permet de récuperer les commande qui viennent d'être modifié pour les mettre dans une variable($entity)
        $entity = $event->getObject();

        // permet de s'assurer que les entité dans la variable(entity) sont bien des commande avant de passer a la suite
        if (!$entity instanceof Commande) {
            return;
        }

        // permet de vérifier quelle champs de la commande ont été modifié pour savoir ce qui a changer et de tout mettre dans une variable(changeSet)
        $changeSet = $this->entityManager->getUnitOfWork()->getEntityChangeSet($entity);

        // permet de s'assurer que le champs modifié est bien le champs statut avant de passer a la suite
        if (!isset($changeSet['statut'])) {
            return;
        }

        // permet d'enregistrer le nouveau statut dans une variable(statutSuivant)(ici 0 est l'ancienne valeur du champs statut et 1 la nouvelle)
        $statutSuivant = $changeSet['statut'][1];

        $statutHistorique = new CommandeStatutHistorique();
        $statutHistorique->setCommande($entity);
        $statutHistorique->setStatutSuivant($statutSuivant);
        $statutHistorique->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($statutHistorique);
        $this->entityManager->flush();
    }
}
