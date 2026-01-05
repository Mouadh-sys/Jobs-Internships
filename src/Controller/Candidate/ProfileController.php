<?php

namespace App\Controller\Candidate;

use App\Entity\Skill;
use App\Entity\User;
use App\Form\UserProfileType;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function edit(
        Request $request, 
        EntityManagerInterface $entityManager,
        SkillRepository $skillRepository,
        SluggerInterface $slugger
    ): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle skills from skillNames field - try multiple ways to get the value
            $skillNamesInput = $form->get('skillNames')->getData();
            
            // Also try to get from request directly
            if (empty($skillNamesInput)) {
                $requestData = $request->request->all();
                $formData = $requestData['user_profile'] ?? [];
                $skillNamesInput = $formData['skillNames'] ?? null;
            }
            
            // Clear existing skills first
            $existingSkills = $user->getSkills()->toArray();
            foreach ($existingSkills as $existingSkill) {
                $user->removeSkill($existingSkill);
            }
            
            // Process skills if we have skill names
            if (!empty($skillNamesInput) && is_string($skillNamesInput)) {
                $skillNames = json_decode($skillNamesInput, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($skillNames) && !empty($skillNames)) {
                    foreach ($skillNames as $skillName) {
                        $skillName = trim((string) $skillName);
                        if (empty($skillName)) {
                            continue;
                        }
                        
                        // Find existing skill by name
                        $skill = $skillRepository->findByName($skillName);
                        
                        // If skill doesn't exist, create it
                        if (!$skill) {
                            $skill = new Skill();
                            $skill->setName($skillName);
                            $slug = strtolower((string) $slugger->slug($skillName));
                            $skill->setSlug($slug);
                            $entityManager->persist($skill);
                        }
                        
                        // Add skill to user
                        $user->addSkill($skill);
                    }
                }
            }

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


