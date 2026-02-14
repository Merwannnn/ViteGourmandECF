<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    public function findByMenuAndThemeFilters(array $filters) : array
    {
        $queryBuilder = $this->createQueryBuilder('m');

        if (!empty($filters['prixMax'])) {
            $queryBuilder->andWhere('m.prixPersonne <= :prixMax')
                ->setParameter('prixMax', $filters['prixMax']);
        }

        if (!empty($filters['prixMin'])) {
            $queryBuilder->andWhere('m.prixPersonne >= :prixMin')
                ->setParameter('prixMin', $filters['prixMin']);
        }

        if (!empty($filters['theme'])) {
            $queryBuilder->andWhere('m.theme = :theme')
                ->setParameter('theme', $filters['theme']);
        }

        if (!empty($filters['regime'])) {
            $queryBuilder->andWhere('m.regime LIKE :regime')
                ->setParameter('regime', '%' . $filters['regime'] . '%');
        }

        if (!empty($filters['personneMin'])) {
            $queryBuilder->andWhere('m.nbPersonneMinimum >= :personneMin')
                ->setParameter('personneMin', $filters['personneMin']);
        }
        
        return $queryBuilder->getQuery()->getResult();
    }

//    /**
//     * @return Menu[] Returns an array of Menu objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Menu
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
