<?php

namespace App\Repository;

use App\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;



/**
 * @extends ServiceEntityRepository<Video>
 *
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[]    findAll()
 * @method Video[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends ServiceEntityRepository
{
    public object $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Video::class);
        $this->paginator = $paginator;
    }

    public function save(Video $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Video $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }




    public function findByChildIds(array $ids, int $page, ?string $sort_method)
    {
        $sort_method = $sort_method != 'rating' ? $sort_method : "ASC";

        $dbquery =  $this->createQueryBuilder('v')
            ->andWhere('v.category IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('v.title', $sort_method)
            ->getQuery();

        $pagination = $this->paginator->paginate($dbquery, $page, 5);

        return $pagination;
    }

    public function findByTitle(string $query, int $page, ?string $sort_method)
    {
        $sort_method = $sort_method != 'rating' ? $sort_method : "ASC";

        $queryBuilder =  $this->createQueryBuilder('v');
        $searchTerms = $this->prepareQuery($query);

        foreach ($searchTerms as $key => $term) {
            $queryBuilder
                ->orWhere('v.title LIKE :_t'.$key)
                ->setParameter('_t'.$key, '%'.trim($term).'%');
        }

        $dbquery = $queryBuilder
            ->orderBy('v.title', $sort_method)
            ->getQuery();
        $pagination = $this->paginator->paginate($dbquery, $page, 5);

        return $pagination;
    }

    private function prepareQuery(string $query)
    {
        return explode(' ', $query);
    }

    //    public function findOneBySomeField($value): ?Video
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
