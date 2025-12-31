<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\User;
use App\Entity\JobOffer;
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

    /**
     * Find applications for a job offer
     * @param JobOffer|int $jobOffer JobOffer entity or ID
     * @return Application[]
     */
    public function findByJobOfferId(JobOffer|int $jobOffer): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($jobOffer instanceof JobOffer) {
            $qb->where('IDENTITY(a.jobOffer) = :jobOfferId')
                ->setParameter('jobOfferId', $jobOffer->getId());
        } else {
            $qb->where('IDENTITY(a.jobOffer) = :jobOfferId')
                ->setParameter('jobOfferId', $jobOffer);
        }

        return $qb->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find an application by job offer and candidate
     * @param JobOffer|int $jobOffer JobOffer entity or ID
     * @param User|int $candidate User entity or ID
     */
    public function findByJobOfferAndCandidate(JobOffer|int $jobOffer, User|int $candidate): ?Application
    {
        $qb = $this->createQueryBuilder('a');

        if ($jobOffer instanceof JobOffer) {
            $qb->andWhere('IDENTITY(a.jobOffer) = :jobOfferId')
                ->setParameter('jobOfferId', $jobOffer->getId());
        } else {
            $qb->andWhere('IDENTITY(a.jobOffer) = :jobOfferId')
                ->setParameter('jobOfferId', $jobOffer);
        }

        if ($candidate instanceof User) {
            $qb->andWhere('IDENTITY(a.candidate) = :candidateId')
                ->setParameter('candidateId', $candidate->getId());
        } else {
            $qb->andWhere('IDENTITY(a.candidate) = :candidateId')
                ->setParameter('candidateId', $candidate);
        }

        return $qb->getQuery()
            ->getOneOrNullResult();
    }
}

