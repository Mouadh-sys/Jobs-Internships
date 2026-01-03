<?php

namespace App\Controller\Company;

use App\Entity\Company;
use App\Form\CompanyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/company/profile')]
#[IsGranted('ROLE_COMPANY')]
class CompanyProfileController extends AbstractController
{
    #[Route('', name: 'company_profile_show', methods: ['GET'])]
    public function show(
        \App\Repository\JobOfferRepository $jobOfferRepository,
        \App\Repository\UserRepository $userRepository
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            return $this->redirectToRoute('app_company_profile_edit');
        }

        // Fetch other companies' offers (active ones)
        // Note: Ideally, we'd have a repository method for "active offers excluding my company", 
        // but finding all active offers is a good start. The view can filter or we can refine the query later.
        $otherOffers = $jobOfferRepository->findActiveOffers();

        // Fetch company's own offers
        $myOffers = $jobOfferRepository->findByCompanyId($company);

        // Fetch candidates
        $candidates = $userRepository->findCandidates();

        return $this->render('company/profile/show.html.twig', [
            'company' => $company,
            'my_offers' => $myOffers,
            'other_offers' => $otherOffers,
            'candidates' => $candidates,
        ]);
    }

    #[Route('/edit', name: 'app_company_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, \Symfony\Component\String\Slugger\SluggerInterface $slugger): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            $company = new Company();
            $company->setUser($user);
        }

        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $logoFile */
            $logoFile = $form->get('logoFilename')->getData();

            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                try {
                    $logoFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/logos',
                        $newFilename
                    );
                } catch (\Symfony\Component\HttpFoundation\File\Exception\FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('error', 'Failed to upload logo.');
                }

                $company->setLogoFilename($newFilename);
            }

            $entityManager->persist($company);
            $entityManager->flush();

            return $this->redirectToRoute('company_profile_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('company/profile/edit.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/status', name: 'company_profile_status', methods: ['GET'])]
    public function status(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        return $this->render('company/profile/status.html.twig', [
            'company' => $company,
        ]);
    }
}


