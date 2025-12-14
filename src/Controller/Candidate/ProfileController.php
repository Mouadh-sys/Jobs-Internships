<?php

namespace App\Controller\Candidate;

use App\Entity\User;
use App\Form\UserProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/candidate/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'candidate_profile_show', methods: ['GET'])]
    public function show(): Response
    {
        // TODO: Show candidate profile
        return $this->render('candidate/profile/show.html.twig', [
            // TODO: Pass user data
        ]);
    }

    #[Route('/edit', name: 'candidate_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO: Edit candidate profile with form
        return $this->render('candidate/profile/edit.html.twig', [
            // TODO: Pass form
        ]);
    }

    #[Route('/cv/download', name: 'candidate_cv_download', methods: ['GET'])]
    public function downloadCv(): Response
    {
        // TODO: Download candidate CV
        return $this->json(['message' => 'CV download not implemented']);
    }
}


