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

    // cette fonction permet de récuperer les menu a l'aide d'un syteme de filtrations
    public function findByMenuAndThemeFilters(array $filters) : array
    {
        $queryBuilder = $this->createQueryBuilder('m');

        // permet de filtrer les menu par le prix maximum
        if (!empty($filters['prixMax'])) {
            $queryBuilder->andWhere('m.prixPersonne <= :prixMax')
                ->setParameter('prixMax', $filters['prixMax']);
        }

        // permet de filtrer les menu par le prix minimum
        if (!empty($filters['prixMin'])) {
            $queryBuilder->andWhere('m.prixPersonne >= :prixMin')
                ->setParameter('prixMin', $filters['prixMin']);
        }

        
        // permet de filtrer les menu par le theme du menu(Noel, paques etc)
        if (!empty($filters['theme'])) {
            $queryBuilder->andWhere('m.theme = :theme')
                ->setParameter('theme', $filters['theme']);
        }

        // permet de filtrer les menu par le regime du menu(Vegan, végétarien etc)
        if (!empty($filters['regime'])) {
            $queryBuilder->andWhere('m.regime LIKE :regime')
                ->setParameter('regime', '%' . $filters['regime'] . '%');
        }

        // permet de filtrer les menu par le nombre de personne minimum nécessaire pour commander
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
