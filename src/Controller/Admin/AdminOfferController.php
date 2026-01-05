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
    public function list(JobOfferRepository $jobOfferRepository, Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $status = $request->query->get('status', '');

        // Use query builder for filtering and pagination
        $qb = $jobOfferRepository->createQueryBuilder('j')
            ->orderBy('j.createdAt', 'DESC');

        // Apply status filter
        if ($status === 'active') {
            $qb->where('j.isActive = :active')
                ->setParameter('active', true);
        } elseif ($status === 'inactive') {
            $qb->where('j.isActive = :active')
                ->setParameter('active', false);
        }
        
        $query = $qb->getQuery();
        $query->setFirstResult($offset)->setMaxResults($limit);
        
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
        $totalOffers = count($paginator);
        $offers = iterator_to_array($paginator);
        
        $totalPages = ceil($totalOffers / $limit);

        return $this->render('admin/offers/list.html.twig', [
            'offers' => $offers,
            'page' => $page,
            'totalPages' => $totalPages,
            'status' => $status,
        ]);
    }

    #[Route('/{id}', name: 'admin_offer_show', methods: ['GET'])]
    public function show(JobOffer $jobOffer): Response
    {
        return $this->render('admin/offers/show.html.twig', [
            'offer' => $jobOffer,
            'applicationCount' => count($jobOffer->getApplications()),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_offer_edit', methods: ['GET', 'POST'])]
    public function edit(JobOffer $jobOffer, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $jobOffer->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($jobOffer);
            $entityManager->flush();

            $this->addFlash('success', 'Job offer updated successfully.');
            return $this->redirectToRoute('admin_offer_show', ['id' => $jobOffer->getId()]);
        }

        return $this->render('admin/offers/form.html.twig', [
            'form' => $form,
            'offer' => $jobOffer,
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_offer_toggle', methods: ['POST'])]
    public function toggle(
        Request $request,
        JobOffer $jobOffer,
        EntityManagerInterface $entityManager,
        AdminLogService $adminLogService
    ): Response {
        // Verify CSRF token
        $tokenId = 'toggle' . $jobOffer->getId();
        if (!$this->isCsrfTokenValid($tokenId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token. Please try again.');
            return $this->redirectToRoute('admin_offers_list');
        }

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
    public function delete(
        Request $request,
        JobOffer $jobOffer,
        EntityManagerInterface $entityManager
    ): Response {
        // Verify CSRF token
        $tokenId = 'delete' . $jobOffer->getId();
        if (!$this->isCsrfTokenValid($tokenId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token. Please try again.');
            return $this->redirectToRoute('admin_offers_list');
        }

        $entityManager->remove($jobOffer);
        $entityManager->flush();

        $this->addFlash('success', 'Job offer deleted successfully.');
        return $this->redirectToRoute('admin_offers_list');
    }
}

