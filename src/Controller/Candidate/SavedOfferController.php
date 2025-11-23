<?php

namespace App\Controller\Candidate;

use App\Entity\JobOffer;
use App\Entity\SavedOffer;
use App\Repository\SavedOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/candidate/saved-offers')]
#[IsGranted('ROLE_USER')]
class SavedOfferController extends AbstractController
{
    #[Route('', name: 'candidate_saved_offers_list', methods: ['GET'])]
    public function list(SavedOfferRepository $savedOfferRepository): Response
    {
        // TODO: List candidate saved offers

        return $this->render('candidate/saved_offers/list.html.twig', [
            // TODO: Pass saved offers
        ]);
    }

    #[Route('/offer/{id}/save', name: 'candidate_save_offer', methods: ['POST'])]
    public function saveOffer(
        JobOffer $jobOffer,
        EntityManagerInterface $entityManager,
        SavedOfferRepository $savedOfferRepository,
    ): Response {
        // TODO: Save job offer to saved list
        // - Check if already saved
        // - Create SavedOffer record

        return $this->redirectToRoute('candidate_offer_detail', ['slug' => $jobOffer->getSlug()]);
    }

    #[Route('/{id}/unsave', name: 'candidate_unsave_offer', methods: ['POST'])]
    public function unsaveOffer(
        SavedOffer $savedOffer,
        EntityManagerInterface $entityManager,
    ): Response {
        // TODO: Remove offer from saved list

        return $this->redirectToRoute('candidate_saved_offers_list');
    }
}

