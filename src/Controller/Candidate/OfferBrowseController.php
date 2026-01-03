<?php

namespace App\Controller\Candidate;

use App\Entity\JobOffer;
use App\Entity\SavedOffer;
use App\Repository\JobOfferRepository;
use App\Repository\CategoryRepository;
use App\Repository\SavedOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/offers')]
class OfferBrowseController extends AbstractController
{
    /**
     * Uses Code 2's logic for filtering and pagination
     */
    #[Route('/', name: 'app_offers_index', methods: ['GET'])]
    public function index(
        Request $request,
        JobOfferRepository $jobOfferRepository,
        CategoryRepository $categoryRepository,
    ): Response {
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $filters = [
            'category' => $request->query->get('category'),
            'location' => $request->query->get('location'),
            'type' => $request->query->get('type'),
            'keyword' => $request->query->get('keyword'),
        ];

        $category = $filters['category'] ? $categoryRepository->find($filters['category']) : null;

        // Logic from Code 2: Proper repository filtering
        $pagination = $jobOfferRepository->searchByFilters(
            $category,
            $filters['location'],
            $filters['type'],
            $filters['keyword'],
            $page,
            $limit
        );

        return $this->render('candidate/offer/index.html.twig', [
            'offers' => $pagination,
            'maxPages' => ceil(count($pagination) / $limit),
            'currentPage' => $page,
            'categories' => $categoryRepository->findAll(),
            'currentFilters' => $filters
        ]);
    }

    /**
     * Logic from Code 1: Includes isSaved check
     */
    #[Route('/{slug}', name: 'candidate_offer_detail', methods: ['GET'])]
    public function detail(JobOffer $jobOffer, SavedOfferRepository $savedOfferRepository): Response
    {
        $user = $this->getUser();
        $isSaved = false;

        if ($user) {
            $savedOffer = $savedOfferRepository->findByUserAndJobOffer($user, $jobOffer->getId());
            $isSaved = $savedOffer !== null;
        }

        return $this->render('candidate/offer/detail.html.twig', [
            'offer' => $jobOffer,
            'isSaved' => $isSaved,
        ]);
    }

    /**
     * Ported from Code 1: Save functionality
     */
    #[Route('/{id}/save', name: 'candidate_offer_save', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function save(JobOffer $jobOffer, EntityManagerInterface $entityManager, SavedOfferRepository $savedOfferRepository): Response
    {
        $user = $this->getUser();

        if ($savedOfferRepository->findByUserAndJobOffer($user, $jobOffer->getId())) {
            $this->addFlash('warning', 'This offer is already saved.');
        } else {
            $savedOffer = new SavedOffer();
            $savedOffer->setUser($user);
            $savedOffer->setJobOffer($jobOffer);
            $entityManager->persist($savedOffer);
            $entityManager->flush();
            $this->addFlash('success', 'Offer saved successfully.');
        }

        return $this->redirectToRoute('candidate_offer_detail', ['slug' => $jobOffer->getSlug()]);
    }

    /**
     * Ported from Code 1: Unsave functionality
     */
    #[Route('/{id}/unsave', name: 'candidate_offer_unsave', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function unsave(JobOffer $jobOffer, EntityManagerInterface $entityManager, SavedOfferRepository $savedOfferRepository): Response
    {
        $savedOffer = $savedOfferRepository->findByUserAndJobOffer($this->getUser(), $jobOffer->getId());

        if ($savedOffer) {
            $entityManager->remove($savedOffer);
            $entityManager->flush();
            $this->addFlash('success', 'Offer removed from saved list.');
        }

        return $this->redirectToRoute('candidate_offer_detail', ['slug' => $jobOffer->getSlug()]);
    }
}
