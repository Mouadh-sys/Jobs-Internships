<?php

namespace App\Controller\Company;

use App\Entity\JobOffer;
use App\Form\JobOfferType;
use App\Repository\JobOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/company/offers')]
#[IsGranted('ROLE_COMPANY')]
class CompanyOfferController extends AbstractController
{
    #[Route('', name: 'company_offers_list', methods: ['GET'])]
    public function list(JobOfferRepository $jobOfferRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            $this->addFlash('error', 'Please complete your company profile first.');
            return $this->redirectToRoute('app_company_profile_edit');
        }

        return $this->render('company/offer/index.html.twig', [
            'offers' => $jobOfferRepository->findByCompanyId($company),
            'company' => $company,
        ]);
    }

    #[Route('/new', name: 'company_offer_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            $this->addFlash('error', 'Please complete your company profile first.');
            return $this->redirectToRoute('app_company_profile_edit');
        }

        $jobOffer = new JobOffer();
        $jobOffer->setCompany($company);

        $form = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($jobOffer->getTitle())->lower() . '-' . uniqid();
            $jobOffer->setSlug($slug);
            $jobOffer->setCreatedAt(new \DateTimeImmutable());
            $jobOffer->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($jobOffer);
            $entityManager->flush();

            $this->addFlash('success', 'Job offer created successfully.');

            return $this->redirectToRoute('company_profile_show');
        }

        return $this->render('company/offer/form.html.twig', [
            'job_offer' => $jobOffer,
            'form' => $form,
            'title' => 'Create Job Offer'
        ]);
    }

    #[Route('/{id}/edit', name: 'company_offer_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        JobOffer $jobOffer,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if ($jobOffer->getCompany() !== $company) {
            throw $this->createAccessDeniedException('You are not allowed to edit this offer.');
        }

        $form = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $jobOffer->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Job offer updated successfully.');

            return $this->redirectToRoute('company_profile_show');
        }

        return $this->render('company/offer/form.html.twig', [
            'job_offer' => $jobOffer,
            'form' => $form,
            'title' => 'Edit Job Offer'
        ]);
    }

    #[Route('/{id}/delete', name: 'company_offer_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        JobOffer $jobOffer,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if ($jobOffer->getCompany() !== $company) {
            throw $this->createAccessDeniedException('You are not allowed to delete this offer.');
        }

        if ($this->isCsrfTokenValid('delete' . $jobOffer->getId(), $request->request->get('_token'))) {
            $entityManager->remove($jobOffer);
            $entityManager->flush();
            $this->addFlash('success', 'Job offer deleted successfully.');
        }

        return $this->redirectToRoute('company_offers_list');
    }
}

