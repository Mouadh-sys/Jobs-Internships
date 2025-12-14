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
    public function list(Request $request, JobOfferRepository $jobOfferRepository): Response
    {
        $status = $request->query->get('status');

        $offers = $jobOfferRepository->findAll();

        // Filter by status
        if ($status === 'active') {
            $offers = array_filter($offers, fn($o) => $o->isActive());
        } elseif ($status === 'inactive') {
            $offers = array_filter($offers, fn($o) => !$o->isActive());
        }

        return $this->render('admin/offers/list.html.twig', [
            'offers' => $offers,
            'status' => $status,
        ]);
    }

    #[Route('/{id}', name: 'admin_offer_show', methods: ['GET'])]
    public function show(JobOffer $jobOffer): Response
    {
        return $this->render('admin/offers/show.html.twig', [
            'offer' => $jobOffer,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_offer_edit', methods: ['GET', 'POST'])]
    public function edit(
        JobOffer $jobOffer,
        Request $request,
        EntityManagerInterface $entityManager,
        AdminLogService $adminLogService,
    ): Response {
        $oldData = [
            'title' => $jobOffer->getTitle(),
            'isActive' => $jobOffer->isActive(),
        ];

        $form = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $jobOffer->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            // Log the action
            $adminLogService->logUpdate($this->getUser(), 'JobOffer', $jobOffer->getId(), [
                'old' => $oldData,
                'new' => [
                    'title' => $jobOffer->getTitle(),
                    'isActive' => $jobOffer->isActive(),
                ],
            ]);

            $this->addFlash('success', 'Job offer updated successfully.');
            return $this->redirectToRoute('admin_offers_list');
        }

        return $this->render('admin/offers/form.html.twig', [
            'form' => $form,
            'offer' => $jobOffer,
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_offer_toggle', methods: ['POST'])]
    public function toggle(
        JobOffer $jobOffer,
        EntityManagerInterface $entityManager,
        AdminLogService $adminLogService,
    ): Response {
        $oldStatus = $jobOffer->isActive();
        $jobOffer->setActive(!$oldStatus);
        $jobOffer->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        // Log the action
        $adminLogService->logUpdate($this->getUser(), 'JobOffer', $jobOffer->getId(), [
            'action' => 'TOGGLE',
            'old_status' => $oldStatus ? 'active' : 'inactive',
            'new_status' => $jobOffer->isActive() ? 'active' : 'inactive',
        ]);

        $this->addFlash('success', 'Job offer status updated successfully.');
        return $this->redirectToRoute('admin_offers_list');
    }

    #[Route('/{id}/delete', name: 'admin_offer_delete', methods: ['POST'])]
    public function delete(
        JobOffer $jobOffer,
        EntityManagerInterface $entityManager,
        AdminLogService $adminLogService,
    ): Response {
        $offerId = $jobOffer->getId();
        $offerTitle = $jobOffer->getTitle();

        $entityManager->remove($jobOffer);
        $entityManager->flush();

        // Log the action
        $adminLogService->logDelete($this->getUser(), 'JobOffer', $offerId, [
            'title' => $offerTitle,
        ]);

        $this->addFlash('success', 'Job offer deleted successfully.');
        return $this->redirectToRoute('admin_offers_list');
    }
}

