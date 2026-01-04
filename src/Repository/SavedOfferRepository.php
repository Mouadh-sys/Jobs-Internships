<?php

namespace App\Repository;

use App\Entity\SavedOffer;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SavedOffer>
 */
class SavedOfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavedOffer::class);
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->setParameter('user', $user)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndJobOffer(User $user, int $jobOfferId): ?SavedOffer
    {
        return $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('IDENTITY(s.jobOffer) = :jobOfferId')
            ->setParameter('user', $user)
            ->setParameter('jobOfferId', $jobOfferId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

