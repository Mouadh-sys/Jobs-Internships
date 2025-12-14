<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\User;
use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Application>
 */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    public function findForCandidate(User $candidate): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.candidate = :candidate')
            ->setParameter('candidate', $candidate)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findForCompany(Company $company): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.jobOffer', 'j')
            ->where('j.company = :company')
            ->setParameter('company', $company)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPendingForCompany(Company $company): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.jobOffer', 'j')
            ->where('j.company = :company')
            ->andWhere('a.status = :status')
            ->setParameter('company', $company)
            ->setParameter('status', Application::STATUS_PENDING)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByJobOfferId(int $jobOfferId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.jobOffer = :jobOffer')
            ->setParameter('jobOffer', $jobOfferId)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByJobOfferAndCandidate(int $jobOfferId, int $candidateId): ?Application
    {
        return $this->createQueryBuilder('a')
            ->where('a.jobOffer = :jobOffer')
            ->andWhere('a.candidate = :candidate')
            ->setParameter('jobOffer', $jobOfferId)
            ->setParameter('candidate', $candidateId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

