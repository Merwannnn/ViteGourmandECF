<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function findMyCommand(User $user) : array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->addSelect('u')
            ->leftJoin('c.menu', 'm')
            ->addSelect('m')
            ->leftJoin('c.avis', 'a')
            ->addSelect('a')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
        
    }

    public function findAllWithUserAndMenuAndFilters(?string $userName = null, ?string $statut = null) : array 
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->addSelect('u')
            ->leftJoin('c.menu', 'm')
            ->addSelect('m')
            ->leftJoin('c.avis', 'a')
            ->addSelect('a')
            ->orderBy('c.dateCommande', 'DESC');

        if ($userName) {
            $queryBuilder->andWhere('u.name LIKE :userName')
                ->setParameter('userName', '%' . $userName . '%');
        }

        if ($statut) {
            $queryBuilder->andWhere('c.statut = :statut')
                ->setParameter('statut', $statut);
        }

        return $queryBuilder->getQuery()->getResult();
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
