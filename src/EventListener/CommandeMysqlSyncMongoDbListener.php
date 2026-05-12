<?php

namespace App\EventListener;

use App\Entity\Commande;
use App\Entity\Menu;
use App\Document\CommandeDocument;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\Event\PreRemoveEventArgs;

class CommandeMysqlSyncMongoDbListener
{
    private array $pendingInserts = [];
    private array $pendingDeletes = [];
    private bool $isProcessing = false;

    public function __construct(private DocumentManager $dm)
    {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Commande) {
            $this->pendingInserts[] = $entity;
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Commande) {
            $this->pendingDeletes[] = $entity->getId();
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        foreach ($this->pendingInserts as $commande) {

            $menu = $commande->getMenu();

            $commandeDoc = new CommandeDocument();
            $commandeDoc->setIdCommandeMysql($commande->getId());
            $commandeDoc->setMenuId($menu->getId());
            $commandeDoc->setMenuName($menu->getTitle());

            $this->dm->persist($commandeDoc);
        }

        foreach ($this->pendingDeletes as $idCommande) {

            $commandeDoc = $this->dm->getRepository(CommandeDocument::class)
                ->findOneBy(['idCommandeMysql' => $idCommande]);

            if ($commandeDoc) {
                $this->dm->remove($commandeDoc);
            }
        }

        $this->dm->flush();

        $this->pendingInserts = [];
        $this->pendingDeletes = [];
        $this->isProcessing = false;
    }
}