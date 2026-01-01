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
            return $this->redirectToRoute('company_profile_show');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 1. Create User
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setRoles(['ROLE_COMPANY']);

            $entityManager->persist($user);

            // 2. Create Company
            $company = new Company();
            $company->setUser($user);
            $company->setName($form->get('companyName')->getData());
            $company->setApproved(true); // Auto-approve for now
            // $company->setLocation('To be updated'); // Optional

            $entityManager->persist($company);
            $entityManager->flush();

            // 3. Auto-login
            $security->login($user, 'form_login', 'main');

            $this->addFlash('success', 'Welcome! Your company account has been created.');

            return $this->redirectToRoute('company_profile_show');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
