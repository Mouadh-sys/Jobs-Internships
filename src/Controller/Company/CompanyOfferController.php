<?php

namespace App\Controller\Company;

use App\Entity\JobOffer;
use App\Form\JobOfferType;
use App\Repository\JobOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/company/offers')]
#[IsGranted('ROLE_COMPANY')]
class CompanyOfferController extends AbstractController
{
    #[Route('', name: 'company_offers_list', methods: ['GET'])]
    public function list(JobOfferRepository $jobOfferRepository): Response
    {
        // TODO: List company job offers with pagination
        // - Show active and inactive offers
        // - Show statistics (applications count)

        return $this->render('company/offers/list.html.twig', [
            // TODO: Pass offers
        ]);
    }

    #[Route('/create', name: 'company_offer_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO: Create new job offer
        // - Use JobOfferType form
        // - Generate slug from title
        // - Set company relationship

        return $this->render('company/offers/form.html.twig', [
            // TODO: Pass form
        ]);
    }

    #[Route('/{id}/edit', name: 'company_offer_edit', methods: ['GET', 'POST'])]
    public function edit(JobOffer $jobOffer, Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO: Edit job offer
        // - Verify ownership
        // - Use JobOfferType form

        return $this->render('company/offers/form.html.twig', [
            'offer' => $jobOffer,
            // TODO: Pass form
        ]);
    }

    #[Route('/{id}/toggle', name: 'company_offer_toggle', methods: ['POST'])]
    public function toggle(JobOffer $jobOffer, EntityManagerInterface $entityManager): Response
    {
        // TODO: Toggle job offer active status

        return $this->redirectToRoute('company_offers_list');
    }

    #[Route('/{id}/delete', name: 'company_offer_delete', methods: ['POST'])]
    public function delete(JobOffer $jobOffer, EntityManagerInterface $entityManager): Response
    {
        // TODO: Delete job offer
        // - Verify ownership
        // - Check if no pending applications

        return $this->redirectToRoute('company_offers_list');
    }

    #[Route('/{id}', name: 'company_offer_show', methods: ['GET'])]
    public function show(JobOffer $jobOffer): Response
    {
        // TODO: Show job offer details with applications stats

        return $this->render('company/offers/show.html.twig', [
            'offer' => $jobOffer,
        ]);
    }

    #[Route('/{id}/applications', name: 'company_offer_applications', methods: ['GET'])]
    public function applications(JobOffer $jobOffer): Response
    {
        // Verify ownership
        $user = $this->getUser();
        if ($jobOffer->getCompany()->getUser() !== $user) {
            throw $this->createAccessDeniedException('You do not have access to this job offer.');
        }

        $applications = $jobOffer->getApplications();

        return $this->render('company/offers/applications.html.twig', [
            'offer' => $jobOffer,
            'applications' => $applications,
        ]);
    }
}

