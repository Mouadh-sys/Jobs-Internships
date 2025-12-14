<?php

namespace App\Repository;

use App\Entity\JobOffer;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobOffer>
 */
class JobOfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobOffer::class);
    }

    public function findActiveOffers(): array
    {
        return $this->createQueryBuilder('j')
            ->where('j.isActive = true')
            ->orderBy('j.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchByFilters(?Category $category = null, ?string $location = null, ?string $type = null, ?string $keyword = null): array
    {
        $qb = $this->createQueryBuilder('j')
            ->where('j.isActive = true');

        if ($category) {
            $qb->andWhere('j.category = :category')
                ->setParameter('category', $category);
        }

        if ($location) {
            $qb->andWhere('j.location LIKE :location')
                ->setParameter('location', '%' . $location . '%');
        }

        if ($type) {
            $qb->andWhere('j.type = :type')
                ->setParameter('type', $type);
        }

        if ($keyword) {
            $qb->andWhere('(j.title LIKE :keyword OR j.description LIKE :keyword)')
                ->setParameter('keyword', '%' . $keyword . '%');
        }

        return $qb->orderBy('j.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCompanyId(int $companyId): array
    {
        return $this->createQueryBuilder('j')
            ->where('j.company = :company')
            ->setParameter('company', $companyId)
            ->orderBy('j.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?JobOffer
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}

