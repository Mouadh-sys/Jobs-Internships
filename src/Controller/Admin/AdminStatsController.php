<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Repository\CompanyRepository;
use App\Repository\JobOfferRepository;
use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/stats')]
#[IsGranted('ROLE_ADMIN')]
class AdminStatsController extends AbstractController
{
    #[Route('', name: 'admin_stats_dashboard', methods: ['GET'])]
    public function dashboard(
        UserRepository $userRepository,
        CompanyRepository $companyRepository,
        JobOfferRepository $jobOfferRepository,
        ApplicationRepository $applicationRepository,
    ): Response {
        $stats = [
            'totalUsers' => $userRepository->count([]),
            'totalCompanies' => $companyRepository->count([]),
            'approvedCompanies' => $companyRepository->count(['isApproved' => true]),
            'pendingCompanies' => $companyRepository->count(['isApproved' => false]),
            'activeCompanies' => $companyRepository->count(['isActive' => true]),
            'inactiveCompanies' => $companyRepository->count(['isActive' => false]),
            'totalOffers' => $jobOfferRepository->count([]),
            'activeOffers' => $jobOfferRepository->count(['isActive' => true]),
            'inactiveOffers' => $jobOfferRepository->count(['isActive' => false]),
            'totalApplications' => $applicationRepository->count([]),
            'pendingApplications' => $applicationRepository->count(['status' => 'PENDING']),
            'acceptedApplications' => $applicationRepository->count(['status' => 'ACCEPTED']),
            'rejectedApplications' => $applicationRepository->count(['status' => 'REJECTED']),
        ];

        return $this->render('admin/stats/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/users', name: 'admin_stats_users', methods: ['GET'])]
    public function userStats(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        $stats = [
            'total' => count($users),
            'byRole' => [
                'ROLE_USER' => 0,
                'ROLE_COMPANY' => 0,
                'ROLE_ADMIN' => 0,
            ],
            'withCompany' => 0,
            'withCV' => 0,
        ];

        foreach ($users as $user) {
            foreach ($user->getRoles() as $role) {
                if (isset($stats['byRole'][$role])) {
                    $stats['byRole'][$role]++;
                }
            }
            if ($user->getCompany() !== null) {
                $stats['withCompany']++;
            }
            if ($user->getCvFilename() !== null) {
                $stats['withCV']++;
            }
        }

        return $this->render('admin/stats/users.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/companies', name: 'admin_stats_companies', methods: ['GET'])]
    public function companyStats(CompanyRepository $companyRepository): Response
    {
        $companies = $companyRepository->findAll();

        $stats = [
            'total' => count($companies),
            'approved' => $companyRepository->count(['isApproved' => true]),
            'pending' => $companyRepository->count(['isApproved' => false]),
            'active' => $companyRepository->count(['isActive' => true]),
            'inactive' => $companyRepository->count(['isActive' => false]),
            'withOffers' => 0,
            'totalOffers' => 0,
        ];

        foreach ($companies as $company) {
            $offersCount = $company->getJobOffers()->count();
            if ($offersCount > 0) {
                $stats['withOffers']++;
                $stats['totalOffers'] += $offersCount;
            }
        }

        return $this->render('admin/stats/companies.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/applications', name: 'admin_stats_applications', methods: ['GET'])]
    public function applicationStats(ApplicationRepository $applicationRepository): Response
    {
        $stats = [
            'total' => $applicationRepository->count([]),
            'pending' => $applicationRepository->count(['status' => 'PENDING']),
            'accepted' => $applicationRepository->count(['status' => 'ACCEPTED']),
            'rejected' => $applicationRepository->count(['status' => 'REJECTED']),
        ];

        return $this->render('admin/stats/applications.html.twig', [
            'stats' => $stats,
        ]);
    }
}

