<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    //    /**
    //     * @return Game[] Returns an array of Game objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Game
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     *
     *
     * @param int $limit The number of games to fetch
     * @return Game[]
     */
    public function findRandomGames(int $limit): array
    {
        $ids = $this->createQueryBuilder('g')
            ->select('g.id')
            ->getQuery()
            ->getScalarResult();

        if (empty($ids)) {
            return [];
        }


        $ids = array_column($ids, 'id');
        shuffle($ids);
        $ids = array_slice($ids, 0, $limit);


        return $this->createQueryBuilder('g')
            ->where('g.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}
