<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
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
    public function list(UserRepository $userRepository): Response
    {
        // TODO: List all users with pagination and filters
        // - Filter by role
        // - Search by email or name

        return $this->render('admin/users/list.html.twig', [
            // TODO: Pass users
        ]);
    }

    #[Route('/create', name: 'admin_user_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        // TODO: Create new user
        // - Use AdminUserType form
        // - Hash password
        // - Send welcome email

        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user);

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
    ): Response {
        // TODO: Edit user
        // - Use AdminUserType form with edit mode
        // - Optional password change

        return $this->render('admin/users/form.html.twig', [
            // TODO: Pass form
            'user' => $user,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        // TODO: Delete user and related data

        return $this->redirectToRoute('admin_users_list');
    }

    #[Route('/{id}', name: 'admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        // TODO: Show user details

        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }
}

