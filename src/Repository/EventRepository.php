<?php

namespace App\Repository;

use App\Entity\Event;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findByCreatedDateAfter(DateTime $date): array
    {
        $qb = $this->createQueryBuilder('e');

        return $qb->where($qb->expr()->gt('e.createdDate', ':date'))
            ->setParameter(':date', $date)
            ->getQuery()
            ->getResult();
    }

    public function FindParticipantById(int $participantId): array
    {


        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('e')
            ->from('App\Entity\Event', 'e')
            ->innerJoin('e.user', 'p')
            ->where('p.id = :participantId')
            ->setParameter('participantId', $participantId);

        $query = $qb->getQuery();
        $events = $query->getResult();

        return $events;
    }


    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
