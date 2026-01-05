<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // 1. Logic from Code 2: Prevent logged-in users from re-logging
        if ($this->getUser()) {
            // Check roles to redirect to the correct dashboard
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_stats_dashboard');
            }
            if ($this->isGranted('ROLE_COMPANY')) {
                return $this->redirectToRoute('company_profile_show');
            }
            return $this->redirectToRoute('candidate_offers_list');
        }

        // 2. Standard Symfony login logic
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
