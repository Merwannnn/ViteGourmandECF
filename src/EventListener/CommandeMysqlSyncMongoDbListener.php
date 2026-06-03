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

// cette class permet d'enregistrer ou de supprimer une commande en base de données MongoDB a chaque fois qu'une commande est créer ou supprimer en base de données MySql(depuis le site)
// seul certain champ de la commande sont enregistrer dans la base de données MongoDB(L'id de la commande et l'id et le titre du menu concerné)
class CommandeMysqlSyncMongoDbListener
{
    // permet de contenir toute les commande en attente d'enregistrement dans la base de données MongoDB
    private array $pendingInserts = [];
    // permet de contenir toute les commande en attente de supression dans la base de données MongoDB
    private array $pendingDeletes = [];
    // permet d'empécher qu'une commande soit enregistrer plusieurs fois en base de données MongoDB en le passant a "true" le moment venu
    private bool $isProcessing = false;

    // permet uniquement d'appeler le DocumentManager pour l'utiliser dans le listener par la suite
    public function __construct(private DocumentManager $dm)
    {
    }

    // cette fonction permet d'utiliser l'évènement doctrine postPersist pour récuperer les commande qui viennent d'être créer et les mettre dans un tableau(pendingInserts)
    public function postPersist(PostPersistEventArgs $args): void
    {
        // permet de récuperer les commande qui viennent d'être créer pour les mettre dans une variable($entity)
        $entity = $args->getObject();

        // permet de s'assurer que les entité dans la variable(entity) sont bien des commande avant de les mettre dans le tableau(pendingInserts)
        if ($entity instanceof Commande) {
            $this->pendingInserts[] = $entity;
        }
    }

    // cette fonction permet d'utiliser l'évènement doctrine preRemove pour récuperer les commande qui viennent d'être supprimer et les mettre dans un tableau(pendingDeletes)
    public function preRemove(PreRemoveEventArgs $args): void
    {
        // permet de récuperer les commande qui viennent d'être supprimer pour les mettre dans une variable($entity)
        $entity = $args->getObject();

        // permet de s'assurer que les entité dans la variable(entity) sont bien des commande avant de les mettre dans le tableau(pendingInserts)
        if ($entity instanceof Commande) {
            $this->pendingDeletes[] = $entity->getId();
        }
    }

    // cette fonction possèdent deux méthode l'une permet d'enregistrer les commande qui viennent d'etre créer en base de données MongoDB
    // et l'autre permet de supprimer les commande dans la base de données MongoDB qui viennent d'etre supprimer de la bdd MySql
    public function postFlush(PostFlushEventArgs $args): void
    {
        // permet comme dit précédemment de s'assurer que cette fonction n'est pas actuellement utiliser pour éviter les doublons
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        // permet de créer un nouveau document pour chacune des commande qui viennent d'etre créer en base de données MySql 
        // avant de les enregistrer dans la base de données MongoDB
        foreach ($this->pendingInserts as $commande) {

            $menu = $commande->getMenu();

            $commandeDoc = new CommandeDocument();
            $commandeDoc->setIdCommandeMysql($commande->getId());
            $commandeDoc->setMenuId($menu->getId());
            $commandeDoc->setMenuName($menu->getTitle());

            $this->dm->persist($commandeDoc);
        }

        // permet de supprimer un document de la base de données MongoDB pour chaque commande qui viennent d'etre supprimer de la base de données MySql 
        // en les ciblant a l'aide de leur id
        foreach ($this->pendingDeletes as $idCommande) {

            $commandeDoc = $this->dm->getRepository(CommandeDocument::class)
                // permet de rechercher le document a supprimer à l'aide l'id de la commande qui vient d'etre supprimer
                ->findOneBy(['idCommandeMysql' => $idCommande]);

            // permet de supprimer le document uniqement si la variable(commandeDoc) n'est pas vide
            if ($commandeDoc) {
                $this->dm->remove($commandeDoc);
            }
        }

        $this->dm->flush();

        // permet de vider les tableau pour éviter d'enregistrer des donnés dans la base de données MongoDB qui on déja été enregistere
        $this->pendingInserts = [];
        $this->pendingDeletes = [];
        // permet de remettre le bool a false pour pouvoir réutiliser la fonction sans probleme
        $this->isProcessing = false;
    }
}