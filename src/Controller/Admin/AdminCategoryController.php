<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/categories')]
#[IsGranted('ROLE_ADMIN')]
class AdminCategoryController extends AbstractController
{
    #[Route('', name: 'admin_categories_list', methods: ['GET'])]
    public function list(CategoryRepository $categoryRepository): Response
    {
        // TODO: List all categories with hierarchy

        return $this->render('admin/categories/list.html.twig', [
            // TODO: Pass categories
        ]);
    }

    #[Route('/create', name: 'admin_category_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO: Create new category
        // - Use CategoryType form
        // - Generate slug from name

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        return $this->render('admin/categories/form.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Category $category, Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO: Edit category
        // - Use CategoryType form

        return $this->render('admin/categories/form.html.twig', [
            // TODO: Pass form
            'category' => $category,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function delete(Category $category, EntityManagerInterface $entityManager): Response
    {
        // TODO: Delete category if no job offers linked

        return $this->redirectToRoute('admin_categories_list');
    }

    #[Route('/{id}', name: 'admin_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        // TODO: Show category details with job offers count

        return $this->render('admin/categories/show.html.twig', [
            'category' => $category,
        ]);
    }
}

