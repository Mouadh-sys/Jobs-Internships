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
        $user = $this->getUser();
        $savedOffers = $savedOfferRepository->findByUser($user);

        return $this->render('candidate/saved_offers/list.html.twig', [
            'savedOffers' => $savedOffers,
        ]);
    }

    #[Route('/offer/{id}/save', name: 'candidate_save_offer', methods: ['POST'])]
    public function saveOffer(
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

    #[Route('/{id}/unsave', name: 'candidate_unsave_offer', methods: ['POST'])]
    public function unsaveOffer(
        SavedOffer $savedOffer,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();

        // Verify ownership
        if ($savedOffer->getUser() !== $user) {
            throw $this->createAccessDeniedException('You do not have access to this saved offer.');
        }

        $entityManager->remove($savedOffer);
        $entityManager->flush();

        $this->addFlash('success', 'Offer removed from saved list.');

        return $this->redirectToRoute('candidate_saved_offers_list');
    }
}

