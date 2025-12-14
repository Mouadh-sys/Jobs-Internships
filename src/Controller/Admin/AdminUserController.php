<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use App\Service\AdminLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('', name: 'admin_users_list', methods: ['GET'])]
    public function list(Request $request, UserRepository $userRepository): Response
    {
        $role = $request->query->get('role');
        $search = $request->query->get('search');
        
        $users = $userRepository->findAll();
        
        // Filter by role
        if ($role) {
            $users = array_filter($users, function(User $user) use ($role) {
                return in_array($role, $user->getRoles(), true);
            });
        }
        
        // Search by email or name
        if ($search) {
            $users = array_filter($users, function(User $user) use ($search) {
                return stripos($user->getEmail(), $search) !== false 
                    || stripos($user->getFullName(), $search) !== false;
            });
        }

        return $this->render('admin/users/list.html.twig', [
            'users' => $users,
            'role' => $role,
            'search' => $search,
        ]);
    }

    #[Route('/create', name: 'admin_user_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        AdminLogService $adminLogService,
    ): Response {
        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user, ['edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash password
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            // Log the action
            $adminLogService->logCreate($this->getUser(), 'User', $user->getId(), [
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ]);

            $this->addFlash('success', 'User created successfully.');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('admin/users/form.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        AdminLogService $adminLogService,
    ): Response {
        $oldData = [
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];
        
        $form = $this->createForm(AdminUserType::class, $user, ['edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update password only if provided
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            // Log the action
            $adminLogService->logUpdate($this->getUser(), 'User', $user->getId(), [
                'old' => $oldData,
                'new' => [
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles(),
                ],
            ]);

            $this->addFlash('success', 'User updated successfully.');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('admin/users/form.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(
        User $user,
        EntityManagerInterface $entityManager,
        AdminLogService $adminLogService,
    ): Response {
        $userId = $user->getId();
        $userEmail = $user->getEmail();

        $entityManager->remove($user);
        $entityManager->flush();

        // Log the action
        $adminLogService->logDelete($this->getUser(), 'User', $userId, [
            'email' => $userEmail,
        ]);

        $this->addFlash('success', 'User deleted successfully.');
        return $this->redirectToRoute('admin_users_list');
    }

    #[Route('/{id}', name: 'admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }
}

