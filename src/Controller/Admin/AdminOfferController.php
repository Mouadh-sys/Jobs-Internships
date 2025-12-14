<?php

namespace App\Controller\Admin;

use App\Entity\JobOffer;
use App\Form\JobOfferType;
use App\Repository\JobOfferRepository;
use App\Service\AdminLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/offers')]
#[IsGranted('ROLE_ADMIN')]
class AdminOfferController extends AbstractController
{
    #[Route('', name: 'admin_offers_list', methods: ['GET'])]
    public function list(JobOfferRepository $jobOfferRepository): Response
    {
        // TODO: List all job offers with pagination
        // - Filter by company
        // - Filter by category
        // - Filter by status

        return $this->render('admin/offers/list.html.twig', [
            // TODO: Pass offers
        ]);
    }

    #[Route('/{id}', name: 'admin_offer_show', methods: ['GET'])]
    public function show(JobOffer $jobOffer): Response
    {
        // TODO: Show job offer details

        return $this->render('admin/offers/show.html.twig', [
            'offer' => $jobOffer,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_offer_edit', methods: ['GET', 'POST'])]
    public function edit(JobOffer $jobOffer, Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO: Edit job offer
        // - Use JobOfferType form

        return $this->render('admin/offers/form.html.twig', [
            // TODO: Pass form
            'offer' => $jobOffer,
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_offer_toggle', methods: ['POST'])]
    public function toggle(
        JobOffer $jobOffer,
        EntityManagerInterface $entityManager,
        AdminLogService $adminLogService
    ): Response {
        $oldStatus = $jobOffer->isActive();
        $jobOffer->setActive(!$oldStatus);
        $jobOffer->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        // Log the action
        $admin = $this->getUser();
        $action = $jobOffer->isActive() ? 'ACTIVATE' : 'DEACTIVATE';
        $adminLogService->log(
            $admin,
            $action,
            'JobOffer',
            (string) $jobOffer->getId(),
            ['previous_status' => $oldStatus, 'new_status' => $jobOffer->isActive()]
        );

        $this->addFlash('success', sprintf(
            'Job offer "%s" has been %s.',
            $jobOffer->getTitle(),
            $jobOffer->isActive() ? 'activated' : 'deactivated'
        ));

        return $this->redirectToRoute('admin_offers_list');
    }

    #[Route('/{id}/delete', name: 'admin_offer_delete', methods: ['POST'])]
    public function delete(JobOffer $jobOffer, EntityManagerInterface $entityManager): Response
    {
        // TODO: Delete job offer

        return $this->redirectToRoute('admin_offers_list');
    }
}

