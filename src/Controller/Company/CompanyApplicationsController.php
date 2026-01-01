<?php

namespace App\Controller\Company;

use App\Entity\Application;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/company/applications')]
#[IsGranted('ROLE_COMPANY')]
class CompanyApplicationsController extends AbstractController
{
    #[Route('', name: 'company_applications_list', methods: ['GET'])]
    public function list(ApplicationRepository $applicationRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            $this->addFlash('error', 'Please complete your company profile first.');
            return $this->redirectToRoute('app_company_profile_edit');
        }

        // Get all applications for this company's job offers
        $applications = $applicationRepository->createQueryBuilder('a')
            ->join('a.jobOffer', 'j')
            ->where('j.company = :company')
            ->setParameter('company', $company)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('company/applications/index.html.twig', [
            'applications' => $applications,
            'company' => $company,
        ]);
    }

    #[Route('/{id}', name: 'company_application_show', methods: ['GET'])]
    public function show(Application $application): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        // Verify this application belongs to one of company's job offers
        if ($application->getJobOffer()->getCompany() !== $company) {
            throw $this->createAccessDeniedException('You are not allowed to view this application.');
        }

        return $this->render('company/applications/show.html.twig', [
            'application' => $application,
        ]);
    }

    #[Route('/{id}/accept', name: 'company_application_accept', methods: ['POST'])]
    public function accept(Application $application, EntityManagerInterface $entityManager, Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        // Verify ownership
        if ($application->getJobOffer()->getCompany() !== $company) {
            throw $this->createAccessDeniedException('You are not allowed to modify this application.');
        }

        // CSRF validation
        if (!$this->isCsrfTokenValid('accept' . $application->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token.');
            return $this->redirectToRoute('company_applications_list');
        }

        $application->setStatus(Application::STATUS_ACCEPTED);
        $application->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        $this->addFlash('success', 'Application accepted successfully.');

        return $this->redirectToRoute('company_applications_list');
    }

    #[Route('/{id}/reject', name: 'company_application_reject', methods: ['POST'])]
    public function reject(Application $application, EntityManagerInterface $entityManager, Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        // Verify ownership
        if ($application->getJobOffer()->getCompany() !== $company) {
            throw $this->createAccessDeniedException('You are not allowed to modify this application.');
        }

        // CSRF validation
        if (!$this->isCsrfTokenValid('reject' . $application->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token.');
            return $this->redirectToRoute('company_applications_list');
        }

        $application->setStatus(Application::STATUS_REJECTED);
        $application->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        $this->addFlash('success', 'Application rejected.');

        return $this->redirectToRoute('company_applications_list');
    }

    #[Route('/{id}/cv/download', name: 'company_application_cv_download', methods: ['GET'])]
    public function downloadCv(Application $application): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        // Verify ownership
        if ($application->getJobOffer()->getCompany() !== $company) {
            throw $this->createAccessDeniedException();
        }

        // Check if application has CV
        if (!$application->getCvFilename()) {
            $this->addFlash('error', 'No CV uploaded for this application.');
            return $this->redirectToRoute('company_application_show', ['id' => $application->getId()]);
        }

        $cvPath = $this->getParameter('kernel.project_dir') . '/public/uploads/cvs/' . $application->getCvFilename();

        if (!file_exists($cvPath)) {
            $this->addFlash('error', 'CV file not found.');
            return $this->redirectToRoute('company_application_show', ['id' => $application->getId()]);
        }

        return $this->file($cvPath);
    }
}

