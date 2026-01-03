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

#[Route('/candidate/offers')]
#[IsGranted('ROLE_USER')]
class OfferBrowseController extends AbstractController
{
    #[Route('', name: 'candidate_offers_list', methods: ['GET'])]
    public function list(
        Request $request,
        JobOfferRepository $jobOfferRepository,
        CategoryRepository $categoryRepository,
    ): Response {
        // TODO: List available job offers with pagination and filters
        // - Category filter
        // - Location filter
        // - Type filter (CDI, CDD, Stage, Freelance)
        // - Keyword search

        $offers = [];
        $categories = $categoryRepository->findRootCategories();

        return $this->render('candidate/offers/list.html.twig', [
            'offers' => $offers,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}/save', name: 'candidate_offer_save', methods: ['POST'])]
    public function save(
        JobOffer $jobOffer,
        EntityManagerInterface $entityManager,
        SavedOfferRepository $savedOfferRepository,
    ): Response {
        $user = $this->getUser();

        // Check if already saved
        $existingSavedOffer = $savedOfferRepository->findByUserAndJobOffer($user, $jobOffer->getId());
        if ($existingSavedOffer) {
            $this->addFlash('warning', 'This offer is already saved.');
            return $this->redirectToRoute('candidate_offer_detail', ['slug' => $jobOffer->getSlug()]);
        }

        // Create SavedOffer record
        $savedOffer = new SavedOffer();
        $savedOffer->setUser($user);
        $savedOffer->setJobOffer($jobOffer);

        $entityManager->persist($savedOffer);
        $entityManager->flush();

        $this->addFlash('success', 'Offer saved successfully.');

        return $this->redirectToRoute('candidate_offer_detail', ['slug' => $jobOffer->getSlug()]);
    }

    #[Route('/{id}/unsave', name: 'candidate_offer_unsave', methods: ['POST'])]
    public function unsave(
        JobOffer $jobOffer,
        EntityManagerInterface $entityManager,
        SavedOfferRepository $savedOfferRepository,
    ): Response {
        $user = $this->getUser();

        $savedOffer = $savedOfferRepository->findByUserAndJobOffer($user, $jobOffer->getId());
        if (!$savedOffer) {
            $this->addFlash('warning', 'This offer is not in your saved list.');
            return $this->redirectToRoute('candidate_offer_detail', ['slug' => $jobOffer->getSlug()]);
        }

        $entityManager->remove($savedOffer);
        $entityManager->flush();

        $this->addFlash('success', 'Offer removed from saved list.');

        return $this->redirectToRoute('candidate_offer_detail', ['slug' => $jobOffer->getSlug()]);
    }

    #[Route('/{slug}', name: 'candidate_offer_detail', methods: ['GET'])]
    public function detail(JobOffer $jobOffer, SavedOfferRepository $savedOfferRepository): Response
    {
        $user = $this->getUser();
        $isSaved = false;

        if ($user) {
            $savedOffer = $savedOfferRepository->findByUserAndJobOffer($user, $jobOffer->getId());
            $isSaved = $savedOffer !== null;
        }

        return $this->render('candidate/offers/detail.html.twig', [
            'offer' => $jobOffer,
            'isSaved' => $isSaved,
        ]);
    }

    #[Route('/search', name: 'candidate_offers_search', methods: ['POST'])]
    public function search(Request $request, JobOfferRepository $jobOfferRepository): Response
    {
        // TODO: Search offers by filters
        $filters = [
            'category' => $request->request->get('category'),
            'location' => $request->request->get('location'),
            'type' => $request->request->get('type'),
            'keyword' => $request->request->get('keyword'),
        ];

        return $this->json([
            'message' => 'Search not implemented',
            'filters' => $filters,
        ]);
    }
}

