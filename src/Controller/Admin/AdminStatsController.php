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
        // TODO: Show admin dashboard with statistics
        // - Total users count
        // - Total companies count (approved/pending)
        // - Total job offers count
        // - Total applications count
        // - Applications by status
        // - Recent activities

        $stats = [
            'totalUsers' => 0,
            'totalCompanies' => 0,
            'approvedCompanies' => 0,
            'pendingCompanies' => 0,
            'totalOffers' => 0,
            'totalApplications' => 0,
        ];

        return $this->render('admin/stats/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/users', name: 'admin_stats_users', methods: ['GET'])]
    public function userStats(UserRepository $userRepository): Response
    {
        // TODO: Show detailed user statistics

        return $this->render('admin/stats/users.html.twig', [
            // TODO: Pass statistics
        ]);
    }

    #[Route('/companies', name: 'admin_stats_companies', methods: ['GET'])]
    public function companyStats(CompanyRepository $companyRepository): Response
    {
        // TODO: Show detailed company statistics

        return $this->render('admin/stats/companies.html.twig', [
            // TODO: Pass statistics
        ]);
    }

    #[Route('/applications', name: 'admin_stats_applications', methods: ['GET'])]
    public function applicationStats(ApplicationRepository $applicationRepository): Response
    {
        // TODO: Show detailed application statistics

        return $this->render('admin/stats/applications.html.twig', [
            // TODO: Pass statistics
        ]);
    }
}

