<?php

namespace App\Controller\Candidate;

use App\Entity\Application;
use App\Entity\JobOffer;
use App\Form\ApplicationType;
use App\Repository\ApplicationRepository;
use App\Service\ApplicationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/candidate/applications')]
#[IsGranted('ROLE_USER')]
class ApplicationController extends AbstractController
{
    #[Route('', name: 'candidate_applications_list', methods: ['GET'])]
    public function list(ApplicationRepository $applicationRepository): Response
    {
        // TODO: List candidate applications with status

        return $this->render('candidate/applications/list.html.twig', [
            // TODO: Pass applications
        ]);
    }

    #[Route('/{id}', name: 'candidate_application_show', methods: ['GET'])]
    public function show(Application $application): Response
    {
        // TODO: Show application details
        // - Display job offer info
        // - Display application status and message

        return $this->render('candidate/applications/show.html.twig', [
            'application' => $application,
        ]);
    }

    #[Route('/offer/{id}/apply', name: 'candidate_apply_offer', methods: ['GET', 'POST'])]
    public function applyToOffer(
        Request $request,
        JobOffer $jobOffer,
        ApplicationService $applicationService,
    ): Response {
        // TODO: Apply to job offer
        // - Show ApplicationType form
        // - Handle file upload
        // - Create application record

        return $this->render('candidate/applications/apply.html.twig', [
            'offer' => $jobOffer,
            // TODO: Pass form
        ]);
    }

    #[Route('/{id}/withdraw', name: 'candidate_application_withdraw', methods: ['POST'])]
    public function withdraw(
        Application $application,
        EntityManagerInterface $entityManager,
    ): Response {
        // TODO: Withdraw application

        return $this->redirectToRoute('candidate_applications_list');
    }
}

