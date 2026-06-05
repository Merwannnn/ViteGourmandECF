<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    // cette fonction permet de récuperer toute les commande client avec les user, menu et avis pour éviter le probleme n+1
    // cette fonction n'est plus utiliser
    public function findAllWithUserAndMenu() : array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->addSelect('u')
            ->leftJoin('c.menu', 'm')
            ->addSelect('m')
            ->leftJoin('c.avis', 'a')
            ->addSelect('a')
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
        
    }

    // cette fonction permet de récuperer uniquement les commande de l'utilisateur connecter avec l'user, les menu, avis et historique des status pour éviter le probleme n+1
    public function findMyCommand(User $user) : array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->addSelect('u')
            ->leftJoin('c.menu', 'm')
            ->addSelect('m')
            ->leftJoin('c.avis', 'a')
            ->addSelect('a')
            ->leftJoin('c.statutHistorique', 's')
            ->addSelect('s')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
        
    }

    // cette fonction permet de récuperer toute les commande client avec les user, menu et avis pour éviter le probleme n+1
    // et également d'utiliser un systeme de filrations
    public function findAllWithUserAndMenuAndFilters(?string $userName = null, ?string $statut = null) : QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->addSelect('u')
            ->leftJoin('c.menu', 'm')
            ->addSelect('m')
            ->leftJoin('c.avis', 'a')
            ->addSelect('a')
            ->orderBy('c.dateCommande', 'DESC');

        // permet de filtrer les commande par le nom d'utilisateur
        if ($userName) {
            $queryBuilder->andWhere('u.name LIKE :userName')
                ->setParameter('userName', '%' . $userName . '%');
        }

        // permet de filtrer les commande par leur statut d'avancement
        if ($statut) {
            $queryBuilder->andWhere('c.statut = :statut')
                ->setParameter('statut', $statut);
        }

        return $queryBuilder;
    }

    // cette fonction permet de calculer le chiffre d'affaire par menu et également de le filtrer par date
    public function findMenuSumAndFilters(Menu $menu, ?\DateTimeInterface $dateStart, ?\DateTimeInterface $dateEnd) : float
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->select('SUM(c.prixMenu) as menuChiffreAffaire')
            ->andWhere('c.menu = :menu')
            ->setParameter('menu', $menu);

        // permet de filtrer le chiffre d'affaire avec une date début
        if ($dateStart) {
            $queryBuilder->andWhere('c.dateCommande >= :dateStart')
            ->setParameter('dateStart', $dateStart);
        }
        
        // permet de filtrer le chiffre d'affaire avec une date de fin
        if ($dateEnd) {
            $queryBuilder->andWhere('c.dateCommande <= :dateEnd')
            ->setParameter('dateEnd', $dateEnd);
        }

        return floatval($queryBuilder->getQuery()->getSingleScalarResult());
    }
//    /**
//     * @return Commande[] Returns an array of Commande objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Commande
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
