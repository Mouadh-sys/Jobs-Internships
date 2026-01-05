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
        $allUsers = $userRepository->findAll();
        $allCompanies = $companyRepository->findAll();
        $allOffers = $jobOfferRepository->findAll();
        $allApplications = $applicationRepository->findAll();

        // Count pending companies
        $pendingCompanies = count(array_filter($allCompanies, fn($c) => !$c->isApproved()));
        $activeCompanies = count(array_filter($allCompanies, fn($c) => $c->isActive()));
        $inactiveCompanies = count(array_filter($allCompanies, fn($c) => !$c->isActive()));

        // Count active/inactive offers
        $activeOffers = count(array_filter($allOffers, fn($o) => $o->isActive()));
        $inactiveOffers = count(array_filter($allOffers, fn($o) => !$o->isActive()));

        // Count applications by status
        $applicationsByStatus = [
            'PENDING' => 0,
            'ACCEPTED' => 0,
            'REJECTED' => 0,
            'WITHDRAWN' => 0,
        ];
        foreach ($allApplications as $app) {
            $status = $app->getStatus();
            if (isset($applicationsByStatus[$status])) {
                $applicationsByStatus[$status]++;
            }
        }

        $stats = [
            'totalUsers' => count($allUsers),
            'totalCompanies' => count($allCompanies),
            'approvedCompanies' => count(array_filter($allCompanies, fn($c) => $c->isApproved())),
            'pendingCompanies' => $pendingCompanies,
            'activeCompanies' => $activeCompanies,
            'inactiveCompanies' => $inactiveCompanies,
            'totalOffers' => count($allOffers),
            'activeOffers' => $activeOffers,
            'inactiveOffers' => $inactiveOffers,
            'totalApplications' => count($allApplications),
            'pendingApplications' => $applicationsByStatus['PENDING'],
            'acceptedApplications' => $applicationsByStatus['ACCEPTED'],
            'rejectedApplications' => $applicationsByStatus['REJECTED'],
            'applicationsByStatus' => $applicationsByStatus,
        ];

        return $this->render('admin/stats/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/users', name: 'admin_stats_users', methods: ['GET'])]
    public function userStats(UserRepository $userRepository): Response
    {
        $allUsers = $userRepository->findAll();

        // Count users by type
        $admins = array_filter($allUsers, fn($u) => in_array('ROLE_ADMIN', $u->getRoles()));
        $companies = array_filter($allUsers, fn($u) => $u->getCompany() !== null);
        $candidates = array_filter($allUsers, fn($u) => $u->getCompany() === null && !in_array('ROLE_ADMIN', $u->getRoles()));

        $stats = [
            'totalUsers' => count($allUsers),
            'admins' => count($admins),
            'companies' => count($companies),
            'candidates' => count($candidates),
        ];

        return $this->render('admin/stats/users.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/companies', name: 'admin_stats_companies', methods: ['GET'])]
    public function companyStats(CompanyRepository $companyRepository): Response
    {
        $allCompanies = $companyRepository->findAll();

        $stats = [
            'totalCompanies' => count($allCompanies),
            'approvedCompanies' => count(array_filter($allCompanies, fn($c) => $c->isApproved())),
            'pendingCompanies' => count(array_filter($allCompanies, fn($c) => !$c->isApproved())),
            'activeCompanies' => count(array_filter($allCompanies, fn($c) => $c->isActive())),
        ];

        return $this->render('admin/stats/companies.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/applications', name: 'admin_stats_applications', methods: ['GET'])]
    public function applicationStats(ApplicationRepository $applicationRepository): Response
    {
        $allApplications = $applicationRepository->findAll();

        // Count applications by status
        $applicationsByStatus = [
            'PENDING' => 0,
            'ACCEPTED' => 0,
            'REJECTED' => 0,
            'WITHDRAWN' => 0,
        ];
        foreach ($allApplications as $app) {
            $status = $app->getStatus();
            if (isset($applicationsByStatus[$status])) {
                $applicationsByStatus[$status]++;
            }
        }

        $stats = [
            'totalApplications' => count($allApplications),
            'byStatus' => $applicationsByStatus,
        ];

        return $this->render('admin/stats/applications.html.twig', [
            'stats' => $stats,
        ]);
    }
}

