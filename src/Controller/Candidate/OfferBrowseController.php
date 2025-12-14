<?php

namespace App\Controller\Candidate;

use App\Entity\JobOffer;
use App\Repository\JobOfferRepository;
use App\Repository\CategoryRepository;
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

    #[Route('/{slug}', name: 'candidate_offer_detail', methods: ['GET'])]
    public function detail(JobOffer $jobOffer): Response
    {
        // TODO: Show job offer details
        // - Display company info
        // - Display applications status
        // - Show save/apply buttons

        return $this->render('candidate/offers/detail.html.twig', [
            'offer' => $jobOffer,
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

