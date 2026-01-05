<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginSuccessHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        if ($this->getUser()) {
            $this->addFlash('warning', 'You are already logged in. Please logout to register a new account.');
            if ($this->isGranted('ROLE_COMPANY')) {
                return $this->redirectToRoute('company_profile_show');
            }
            return $this->redirectToRoute('candidate_profile_show');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Debug: Log form state
        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = [];
            // Get all form errors recursively
            $formErrors = $form->getErrors(true);
            foreach ($formErrors as $error) {
                if (method_exists($error, 'getMessage')) {
                    $errors[] = $error->getMessage();
                }
            }
            // Also check individual field errors
            foreach ($form->all() as $child) {
                $childErrors = $child->getErrors();
                foreach ($childErrors as $error) {
                    if (method_exists($error, 'getMessage')) {
                        $errors[] = ucfirst($child->getName()) . ': ' . $error->getMessage();
                    }
                }
            }
            if (!empty($errors)) {
                $this->addFlash('error', 'Please fix the following errors: ' . implode(', ', array_unique($errors)));
            } else {
                $this->addFlash('error', 'Please check all fields and try again.');
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $accountType = $form->get('accountType')->getData();
            $companyName = $form->get('companyName')->getData();

            // Validate company name if account type is company
            if ($accountType === 'company' && empty($companyName)) {
                $form->get('companyName')->addError(new \Symfony\Component\Form\FormError('Company name is required for company accounts.'));
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // 1. Create User
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            if ($accountType === 'company') {
                $user->setRoles(['ROLE_COMPANY']);
                $entityManager->persist($user);

                // 2. Create Company (not auto-approved)
                $company = new Company();
                $company->setUser($user);
                $company->setName($companyName);
                $company->setApproved(false); // Require admin approval
                $company->setActive(false);

                $entityManager->persist($company);
                $entityManager->flush();

                // 3. Auto-login
                $security->login($user, 'form_login', 'main');

                $this->addFlash('success', 'Welcome! Your company account has been created and is pending approval.');

                return $this->redirectToRoute('company_profile_show');
            } else {
                // Candidate registration
                $user->setRoles(['ROLE_USER']);
                $entityManager->persist($user);
                $entityManager->flush();

                // 3. Auto-login
                $security->login($user, 'form_login', 'main');

                $this->addFlash('success', 'Welcome! Your candidate account has been created.');

                return $this->redirectToRoute('candidate_profile_show');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
