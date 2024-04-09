<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

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


    public function findByNameAndDates($searchQuery, $startDate, $endDate)
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.name LIKE :searchQuery')
            ->setParameter('searchQuery', "%$searchQuery%")
            ->andWhere('e.firstAirDate >= :startDate')
            ->setParameter('startDate', $startDate)
            ->andWhere('e.firstAirDate <= :endDate')
            ->setParameter('endDate', $endDate)
            ->orderBy('e.id', 'ASC')
            ->getQuery();

        return $qb->getResult();
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
        $event = $query->getResult();
        return $event;
    }

    public function findByName($name)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findEventsBetweenDates($startDate, $endDate)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.firstAirDate >= :startDate')
            ->andWhere('e.firstAirDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findByOrganizer(User $user)
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.organizer', 'o')
            ->where('o.id = :organizerId')
            ->setParameter('organizerId', $user->getId())
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findEventsWithParticipant($participant)
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.user', 'p')
            ->where('p.id = :participantId')
            ->setParameter('participantId', $participant)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }



}
