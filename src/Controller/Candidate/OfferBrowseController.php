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

#[Route('/offers')]
class OfferBrowseController extends AbstractController
{
    #[Route('/', name: 'app_offers_index', methods: ['GET'])]
    public function index(
        Request $request,
        JobOfferRepository $jobOfferRepository,
        CategoryRepository $categoryRepository,
    ): Response {
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $categoryId = $request->query->get('category');
        $location = $request->query->get('location');
        $type = $request->query->get('type');
        $keyword = $request->query->get('keyword');

        $category = null;
        if ($categoryId) {
            $category = $categoryRepository->find($categoryId);
        }

        $pagination = $jobOfferRepository->searchByFilters($category, $location, $type, $keyword, $page, $limit);

        $maxPages = ceil(count($pagination) / $limit);

        return $this->render('candidate/offer/index.html.twig', [
            'offers' => $pagination,
            'maxPages' => $maxPages,
            'currentPage' => $page,
            'categories' => $categoryRepository->findAll(),
            'currentFilters' => [
                'category' => $categoryId,
                'location' => $location,
                'type' => $type,
                'keyword' => $keyword,
            ]
        ]);
    }

    #[Route('/{slug}', name: 'candidate_offer_detail', methods: ['GET'])]
    public function detail(JobOffer $jobOffer): Response
    {
        return $this->render('candidate/offer/detail.html.twig', [
            'offer' => $jobOffer,
        ]);
    }
}

