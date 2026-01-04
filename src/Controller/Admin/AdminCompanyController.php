<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Form\AdminCompanyType;
use App\Repository\CompanyRepository;
use App\Service\CompanyApprovalService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/companies')]
#[IsGranted('ROLE_ADMIN')]
class AdminCompanyController extends AbstractController
{
    #[Route('', name: 'admin_companies_list', methods: ['GET'])]
    public function list(CompanyRepository $companyRepository): Response
    {
        // TODO: List all companies with pagination
        // - Show approval status
        // - Show active status
        // - Filter by status

        return $this->render('admin/companies/list.html.twig', [
            // TODO: Pass companies
        ]);
    }

    #[Route('/pending', name: 'admin_companies_pending', methods: ['GET'])]
    public function pending(CompanyRepository $companyRepository): Response
    {
        // TODO: List pending companies for approval

        return $this->render('admin/companies/pending.html.twig', [
            // TODO: Pass pending companies
        ]);
    }

    #[Route('/{id}', name: 'admin_company_show', methods: ['GET'])]
    public function show(Company $company): Response
    {
        // TODO: Show company details

        return $this->render('admin/companies/show.html.twig', [
            'company' => $company,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_company_edit', methods: ['GET', 'POST'])]
    public function edit(Company $company, Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO: Edit company
        // - Use AdminCompanyType form

        return $this->render('admin/companies/form.html.twig', [
            // TODO: Pass form
            'company' => $company,
        ]);
    }

    #[Route('/{id}/approve', name: 'admin_company_approve', methods: ['POST'])]
    public function approve(
        Company $company,
        CompanyApprovalService $approvalService,
    ): Response {
        /** @var \App\Entity\User $admin */
        $admin = $this->getUser();
        $approvalService->approve($company, $admin);

        $this->addFlash('success', 'Company approved successfully.');
        return $this->redirectToRoute('admin_companies_pending');
    }

    #[Route('/{id}/reject', name: 'admin_company_reject', methods: ['POST'])]
    public function reject(
        Company $company,
        Request $request,
        CompanyApprovalService $approvalService,
    ): Response {
        /** @var \App\Entity\User $admin */
        $admin = $this->getUser();
        $reason = $request->request->get('reason', '');

        $approvalService->reject($company, $admin, $reason);

        $this->addFlash('success', 'Company rejected successfully.');
        return $this->redirectToRoute('admin_companies_pending');
    }

    #[Route('/{id}/delete', name: 'admin_company_delete', methods: ['POST'])]
    public function delete(Company $company, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($company);
        $entityManager->flush();

        $this->addFlash('success', 'Company deleted successfully.');
        return $this->redirectToRoute('admin_companies_list');
    }
}

