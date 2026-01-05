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

    public function searchByFilters(?Category $category = null, ?string $location = null, ?string $type = null, ?string $keyword = null, int $page = 1, int $limit = 10): \Doctrine\ORM\Tools\Pagination\Paginator
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

        $query = $qb->orderBy('j.createdAt', 'DESC')
            ->getQuery();

        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new \Doctrine\ORM\Tools\Pagination\Paginator($query);
    }

    public function findByCompanyId(\App\Entity\Company $company): array
    {
        return $this->createQueryBuilder('j')
            ->where('j.company = :company')
            ->setParameter('company', $company)
            ->orderBy('j.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?JobOffer
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}

