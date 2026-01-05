<?php

namespace App\Repository;

use App\Entity\AdminLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdminLog>
 */
class AdminLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminLog::class);
    }

    public function findRecent(int $limit = 50): array
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByEntityType(string $entityType): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.entityType = :type')
            ->setParameter('type', $entityType)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByAction(string $action): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.action = :action')
            ->setParameter('action', $action)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

