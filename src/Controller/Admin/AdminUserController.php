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
    public function list(UserRepository $userRepository, Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $role = $request->query->get('role', '');

        // Use query builder for filtering and pagination
        $qb = $userRepository->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC');

        if ($role === 'admin') {
            $qb->where("JSON_CONTAINS(u.roles, :role) = 1")
                ->setParameter('role', json_encode('ROLE_ADMIN'));
        } elseif ($role === 'company') {
            $qb->where('u.company IS NOT NULL');
        } elseif ($role === 'candidate') {
            $qb->leftJoin('App\Entity\Company', 'c', 'WITH', 'c.user = u')
                ->where('c.id IS NULL')
                ->andWhere("u.roles NOT LIKE '%ROLE_ADMIN%'");
        }

        $query = $qb->getQuery();
        $query->setFirstResult($offset)->setMaxResults($limit);
        
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
        $totalUsers = count($paginator);
        $users = iterator_to_array($paginator);
        
        $totalPages = ceil($totalUsers / $limit);

        return $this->render('admin/users/list.html.twig', [
            'users' => $users,
            'page' => $page,
            'totalPages' => $totalPages,
            'role' => $role,
        ]);
    }

    #[Route('/create', name: 'admin_user_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User created successfully.');
            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
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
    ): Response {
        $form = $this->createForm(AdminUserType::class, $user, ['edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password if provided
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User updated successfully.');
            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
        }

        return $this->render('admin/users/form.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        // Verify CSRF token
        $tokenId = 'delete' . $user->getId();
        if (!$this->isCsrfTokenValid($tokenId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token. Please try again.');
            return $this->redirectToRoute('admin_users_list');
        }

        // Remove user (cascade delete will handle applications and saved offers)
        $entityManager->remove($user);
        $entityManager->flush();

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

