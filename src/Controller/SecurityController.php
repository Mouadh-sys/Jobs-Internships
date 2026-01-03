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
            if ($this->isGranted('ROLE_COMPANY')) {
                return $this->redirectToRoute('company_dashboard'); // Or your company route
            }
            return $this->redirectToRoute('candidate_offers_list'); // Or your candidate route
        }

        // 2. Standard Symfony login logic
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {
        // Logic from Code 1: Explicitly defining the method is cleaner
        throw new \LogicException('This should never be reached.');
    }
}
