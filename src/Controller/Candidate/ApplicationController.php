<?php

namespace App\Controller\Candidate;

use App\Entity\Application;
use App\Entity\JobOffer;
use App\Form\ApplicationType;
use App\Repository\ApplicationRepository;
use App\Service\ApplicationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/candidate/applications')]
#[IsGranted('ROLE_USER')]
class ApplicationController extends AbstractController
{
    #[Route('', name: 'candidate_applications_list', methods: ['GET'])]
    public function list(ApplicationRepository $applicationRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $applications = $applicationRepository->findForCandidate($user);

        return $this->render('candidate/applications/list.html.twig', [
            'applications' => $applications,
        ]);
    }

    #[Route('/{id}', name: 'candidate_application_show', methods: ['GET'])]
    public function show(Application $application): Response
    {
        // Verify ownership
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($application->getCandidate() !== $user) {
            throw $this->createAccessDeniedException('You can only view your own applications.');
        }

        return $this->render('candidate/applications/show.html.twig', [
            'application' => $application,
        ]);
    }

    #[Route('/offer/{id}/apply', name: 'candidate_apply_offer', methods: ['GET', 'POST'])]
    public function applyToOffer(
        Request $request,
        JobOffer $jobOffer,
        ApplicationService $applicationService,
    ): Response {
        $form = $this->createForm(ApplicationType::class, new Application());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var \App\Entity\User $user */
                $user = $this->getUser();
                $message = $form->get('message')->getData() ?? '';
                $cv = $form->get('cvFilename')->getData();

                $application = $applicationService->applyToOffer(
                    $user,
                    $jobOffer,
                    $message,
                    $cv,
                );

                $this->addFlash('success', 'Your application has been submitted successfully.');
                return $this->redirectToRoute('candidate_application_show', ['id' => $application->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error: ' . $e->getMessage());
            }
        }

        return $this->render('candidate/applications/apply.html.twig', [
            'offer' => $jobOffer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/withdraw', name: 'candidate_application_withdraw', methods: ['POST'])]
    public function withdraw(
        Application $application,
        EntityManagerInterface $entityManager,
    ): Response {
        // Verify that the logged-in candidate owns this application
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($application->getCandidate() !== $user) {
            $this->addFlash('error', 'You can only withdraw your own applications.');
            return $this->redirectToRoute('candidate_applications_list');
        }

        // Prevent withdrawal if already accepted or rejected
        if ($application->isAccepted() || $application->isRejected()) {
            $this->addFlash('error', 'You cannot withdraw an application that has already been ' . strtolower($application->getStatus()) . '.');
            return $this->redirectToRoute('candidate_application_show', ['id' => $application->getId()]);
        }

        // Mark application as withdrawn
        $application->setStatus(Application::STATUS_WITHDRAWN);
        $application->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($application);
        $entityManager->flush();

        $this->addFlash('success', 'Application withdrawn successfully.');
        return $this->redirectToRoute('candidate_applications_list');
    }
}

