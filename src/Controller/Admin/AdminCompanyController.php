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
    public function list(CompanyRepository $companyRepository, Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $status = $request->query->get('status', '');

        // Use query builder for filtering and pagination
        $qb = $companyRepository->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC');

        if ($status === 'approved') {
            $qb->where('c.isApproved = :approved')
                ->setParameter('approved', true);
        } elseif ($status === 'pending') {
            $qb->where('c.isApproved = :approved')
                ->setParameter('approved', false);
        } elseif ($status === 'active') {
            $qb->where('c.isActive = :active')
                ->setParameter('active', true);
        } elseif ($status === 'inactive') {
            $qb->where('c.isActive = :active')
                ->setParameter('active', false);
        }

        $query = $qb->getQuery();
        $query->setFirstResult($offset)->setMaxResults($limit);
        
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
        $totalFiltered = count($paginator);
        $companies = iterator_to_array($paginator);
        
        $totalPages = ceil($totalFiltered / $limit);

        return $this->render('admin/companies/list.html.twig', [
            'companies' => $companies,
            'page' => $page,
            'totalPages' => $totalPages,
            'status' => $status,
        ]);
    }

    #[Route('/pending', name: 'admin_companies_pending', methods: ['GET'])]
    public function pending(CompanyRepository $companyRepository): Response
    {
        $companies = $companyRepository->findPendingCompanies();

        return $this->render('admin/companies/pending.html.twig', [
            'companies' => $companies,
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
    public function edit(Company $company, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminCompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($company);
            $entityManager->flush();

            $this->addFlash('success', 'Company updated successfully.');
            return $this->redirectToRoute('admin_company_show', ['id' => $company->getId()]);
        }

        return $this->render('admin/companies/form.html.twig', [
            'form' => $form,
            'company' => $company,
        ]);
    }

    #[Route('/{id}/approve', name: 'admin_company_approve', methods: ['POST'])]
    public function approve(
        Request $request,
        Company $company,
        CompanyApprovalService $approvalService,
    ): Response {
        // Verify CSRF token
        $tokenId = 'approve' . $company->getId();
        if (!$this->isCsrfTokenValid($tokenId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token. Please try again.');
            return $this->redirectToRoute('admin_companies_pending');
        }

        /** @var \App\Entity\User $admin */
        $admin = $this->getUser();
        $approvalService->approve($company, $admin);

        $this->addFlash('success', 'Company approved successfully.');
        return $this->redirectToRoute('admin_companies_pending');
    }

    #[Route('/{id}/reject', name: 'admin_company_reject', methods: ['POST'])]
    public function reject(
        Request $request,
        Company $company,
        CompanyApprovalService $approvalService,
    ): Response {
        // Verify CSRF token
        $tokenId = 'reject' . $company->getId();
        if (!$this->isCsrfTokenValid($tokenId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token. Please try again.');
            return $this->redirectToRoute('admin_companies_pending');
        }

        /** @var \App\Entity\User $admin */
        $admin = $this->getUser();
        $reason = $request->request->get('reason', '');

        $approvalService->reject($company, $admin, $reason);

        $this->addFlash('success', 'Company rejected successfully.');
        return $this->redirectToRoute('admin_companies_pending');
    }

    #[Route('/{id}/delete', name: 'admin_company_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Company $company,
        EntityManagerInterface $entityManager,
    ): Response {
        // Verify CSRF token
        $tokenId = 'delete' . $company->getId();
        if (!$this->isCsrfTokenValid($tokenId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token. Please try again.');
            return $this->redirectToRoute('admin_companies_list');
        }

        $entityManager->remove($company);
        $entityManager->flush();

        $this->addFlash('success', 'Company deleted successfully.');
        return $this->redirectToRoute('admin_companies_list');
    }
}

