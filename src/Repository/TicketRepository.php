<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Model\SearchData;

/**
 * @extends ServiceEntityRepository<Ticket>
 *
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function findBySearch(SearchData $searchData): array
    {
        $queryBuilder = $this->createQueryBuilder("t");
    
        if (!empty($searchData->q)) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like('t.title', ':q'))
                ->orWhere($queryBuilder->expr()->like('t.content', ':q'))
                ->setParameter('q', "%{$searchData->q}%");
        }
        if(!empty($searchData->tags)){
            $queryBuilder = $queryBuilder
                ->join("t.tags","tag")
                ->andWhere("tag.id IN (:tags)")
                ->setParameter('tags', $searchData->tags);
        }
    
        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

//     * @return Ticket[] Returns an array of Ticket objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Ticket
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
