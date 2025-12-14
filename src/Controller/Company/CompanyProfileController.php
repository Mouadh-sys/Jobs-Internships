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
    public function show(): Response
    {
        // TODO: Show company profile
        return $this->render('company/profile/show.html.twig', [
            // TODO: Pass company data
        ]);
    }

    #[Route('/edit', name: 'company_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        // - Use CompanyType form
        // - Handle logo upload
        // TODO: Edit company profile
        return $this->render('company/profile/edit.html.twig', [
            // TODO: Pass form
        ]);
    }

    #[Route('/status', name: 'company_profile_status', methods: ['GET'])]
    public function status(): Response
    {
        // TODO: Show company approval status
        // - If approved, show success message
        // - If pending, show waiting message
        // - If rejected, show reason
        return $this->render('company/profile/status.html.twig', [
            // TODO: Pass company and status info
        ]);
    }
}


