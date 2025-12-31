<?php

namespace App\Controller\Candidate;

use App\Entity\User;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
        $user = $this->getUser();

        return $this->render('candidate/profile/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit', name: 'candidate_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle CV upload
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('cvFilename')->getData();
            if ($uploadedFile) {
                $newFilename = uniqid().'.'.$uploadedFile->guessExtension();
                $uploadedFile->move($this->getParameter('cv_directory'), $newFilename);
                $user->setCvFilename($newFilename);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully!');

            return $this->redirectToRoute('candidate_profile_show');
        }

        return $this->render('candidate/profile/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/cv/download', name: 'candidate_cv_download', methods: ['GET'])]
    public function downloadCv(): Response
    {
        $user = $this->getUser();
        if (!$user->getCvFilename()) {
            $this->addFlash('error', 'No CV uploaded');
            return $this->redirectToRoute('candidate_profile_show');
        }

        $cvPath = $this->getParameter('cv_directory') . '/' . $user->getCvFilename();
        return $this->file($cvPath, $user->getCvFilename());
    }
}


