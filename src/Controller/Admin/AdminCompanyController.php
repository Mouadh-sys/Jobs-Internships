<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Form\AdminCompanyType;
use App\Repository\CompanyRepository;
use App\Service\AdminLogService;
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
    public function list(Request $request, CompanyRepository $companyRepository): Response
    {
        $status = $request->query->get('status');

        $companies = $companyRepository->findAll();

        // Filter by status
        if ($status === 'approved') {
            $companies = array_filter($companies, fn($c) => $c->isApproved());
        } elseif ($status === 'pending') {
            $companies = array_filter($companies, fn($c) => !$c->isApproved());
        } elseif ($status === 'active') {
            $companies = array_filter($companies, fn($c) => $c->isActive());
        } elseif ($status === 'inactive') {
            $companies = array_filter($companies, fn($c) => !$c->isActive());
        }

        return $this->render('admin/companies/list.html.twig', [
            'companies' => $companies,
            'status' => $status,
        ]);
    }

    #[Route('/pending', name: 'admin_companies_pending', methods: ['GET'])]
    public function pending(CompanyRepository $companyRepository): Response
    {
        $pendingCompanies = $companyRepository->findBy(['isApproved' => false]);

        return $this->render('admin/companies/pending.html.twig', [
            'companies' => $pendingCompanies,
        ]);
    }

    #[Route('/{id}', name: 'admin_company_show', methods: ['GET'])]
    public function show(Company $company): Response
    {
        return $this->render('admin/companies/show.html.twig', [
            'company' => $company,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_company_edit', methods: ['GET', 'POST'])]
    public function edit(
        Company $company,
        Request $request,
        EntityManagerInterface $entityManager,
        AdminLogService $adminLogService,
    ): Response {
        $oldData = [
            'name' => $company->getName(),
            'isApproved' => $company->isApproved(),
            'isActive' => $company->isActive(),
        ];

        $form = $this->createForm(AdminCompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Log the action
            $adminLogService->logUpdate($this->getUser(), 'Company', $company->getId(), [
                'old' => $oldData,
                'new' => [
                    'name' => $company->getName(),
                    'isApproved' => $company->isApproved(),
                    'isActive' => $company->isActive(),
                ],
            ]);

            $this->addFlash('success', 'Company updated successfully.');
            return $this->redirectToRoute('admin_companies_list');
        }

        return $this->render('admin/companies/form.html.twig', [
            'form' => $form,
            'company' => $company,
        ]);
    }

    #[Route('/{id}/approve', name: 'admin_company_approve', methods: ['POST'])]
    public function approve(
        Company $company,
        CompanyApprovalService $approvalService,
    ): Response {
        $approvalService->approve($company, $this->getUser());
        $this->addFlash('success', 'Company approved successfully.');
        return $this->redirectToRoute('admin_companies_pending');
    }

    #[Route('/{id}/reject', name: 'admin_company_reject', methods: ['POST'])]
    public function reject(
        Company $company,
        Request $request,
        CompanyApprovalService $approvalService,
    ): Response {
        $reason = $request->request->get('reason', '');
        $approvalService->reject($company, $this->getUser(), $reason);
        $this->addFlash('success', 'Company rejected successfully.');
        return $this->redirectToRoute('admin_companies_pending');
    }

    #[Route('/{id}/delete', name: 'admin_company_delete', methods: ['POST'])]
    public function delete(
        Company $company,
        EntityManagerInterface $entityManager,
        AdminLogService $adminLogService,
    ): Response {
        $companyId = $company->getId();
        $companyName = $company->getName();

        $entityManager->remove($company);
        $entityManager->flush();

        // Log the action
        $adminLogService->logDelete($this->getUser(), 'Company', $companyId, [
            'name' => $companyName,
        ]);

        $this->addFlash('success', 'Company deleted successfully.');
        return $this->redirectToRoute('admin_companies_list');
    }
}

