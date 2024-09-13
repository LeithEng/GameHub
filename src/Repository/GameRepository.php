<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    public function findByFilters(array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('g');

        if (isset($filters['genre'])) {
            $qb->andWhere('g.genre = :genre')
                ->setParameter('genre', $filters['genre']);
        }

        if (isset($filters['publisher'])) {
            $qb->andWhere('g.publisher = :publisher')
                ->setParameter('publisher', $filters['publisher']);
        }

        if (isset($filters['price_min']) && isset($filters['price_max'])) {
            $qb->andWhere('g.price BETWEEN :price_min AND :price_max')
                ->setParameter('price_min', $filters['price_min'])
                ->setParameter('price_max', $filters['price_max']);
        } elseif (isset($filters['price_min'])) {
            $qb->andWhere('g.price >= :price_min')
                ->setParameter('price_min', $filters['price_min']);
        } elseif (isset($filters['price_max'])) {
            $qb->andWhere('g.price <= :price_max')
                ->setParameter('price_max', $filters['price_max']);
        }



        return $qb;
    }


    public function getGenres(): array
    {
        return array_column($this->createQueryBuilder('g')
            ->select('DISTINCT g.genre')
            ->getQuery()
            ->getScalarResult(), 'genre');
    }

    public function getPublishers(): array
    {
        return array_column($this->createQueryBuilder('g')
            ->select('DISTINCT g.publisher')
            ->getQuery()
            ->getScalarResult(), 'publisher');
    }
    public function findByQuery(string $query)
    {
        return $this->createQueryBuilder('g')
            ->where('g.title LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }
}
