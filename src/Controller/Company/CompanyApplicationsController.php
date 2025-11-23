<?php

namespace App\Controller\Company;

use App\Entity\Application;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/company/applications')]
#[IsGranted('ROLE_COMPANY')]
class CompanyApplicationsController extends AbstractController
{
    #[Route('', name: 'company_applications_list', methods: ['GET'])]
    public function list(ApplicationRepository $applicationRepository): Response
    {
        // TODO: List applications for company job offers
        // - Filter by job offer
        // - Filter by status (pending, accepted, rejected)
        // - Pagination

        return $this->render('company/applications/list.html.twig', [
            // TODO: Pass applications
        ]);
    }

    #[Route('/{id}', name: 'company_application_show', methods: ['GET'])]
    public function show(Application $application): Response
    {
        // TODO: Show application details
        // - Candidate info
        // - Cover letter
        // - CV download
        // - Accept/Reject buttons

        return $this->render('company/applications/show.html.twig', [
            'application' => $application,
        ]);
    }

    #[Route('/{id}/accept', name: 'company_application_accept', methods: ['POST'])]
    public function accept(Application $application, EntityManagerInterface $entityManager): Response
    {
        // TODO: Accept application
        // - Update status to ACCEPTED
        // - Send email to candidate
        // - Log action

        return $this->redirectToRoute('company_applications_list');
    }

    #[Route('/{id}/reject', name: 'company_application_reject', methods: ['POST'])]
    public function reject(Application $application, EntityManagerInterface $entityManager): Response
    {
        // TODO: Reject application
        // - Update status to REJECTED
        // - Send email to candidate
        // - Log action

        return $this->redirectToRoute('company_applications_list');
    }

    #[Route('/{id}/cv/download', name: 'company_application_cv_download', methods: ['GET'])]
    public function downloadCv(Application $application): Response
    {
        // TODO: Download candidate CV from application

        return $this->json(['message' => 'CV download not implemented']);
    }
}

