<?php

namespace App\Repository;

use App\Entity\Thread;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Thread>
 *
 * @method Thread|null find($id, $lockMode = null, $lockVersion = null)
 * @method Thread|null findOneBy(array $criteria, array $orderBy = null)
 * @method Thread[]    findAll()
 * @method Thread[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThreadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thread::class);
    }

    public function findThreadsByForumId(int $forumId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.forum = :forumId')
            ->setParameter('forumId', $forumId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find threads by forum ID, excluding special threads.
     *
     * @param int $forumId
     * @return array
     */
    public function findRegularThreadsByForumId(int $forumId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.forum = :forumId')
            ->andWhere('t.isSpecial = :isSpecial')
            ->setParameter('forumId', $forumId)
            ->setParameter('isSpecial', 'no') // 'no' indicates regular threads
            ->getQuery()
            ->getResult();
    }

    /**
     * Find scheduled threads to publish.
     *
     * @return Thread[] Returns an array of Thread objects
     */
    public function findScheduledThreadsToPublish(): array
    {
        $qb = $this->createQueryBuilder('t');

        $qb->where('t.isSpecial = :isSpecial')
            ->andWhere($qb->expr()->lte('t.scheduled_publish_time', ':currentDate'))
            ->andWhere('t.Published = :published')
            ->setParameter('isSpecial', 'yes')
            ->setParameter('published', 'yes')
            ->setParameter('currentDate', new \DateTime(), \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE);


        return $qb->getQuery()->getResult();
    }


    //    /**
    //     * @return Thread[] Returns an array of Thread objects
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

    //    public function findOneBySomeField($value): ?Thread
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
