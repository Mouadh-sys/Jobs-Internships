<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\AdminLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/categories')]
#[IsGranted('ROLE_ADMIN')]
class AdminCategoryController extends AbstractController
{
    #[Route('', name: 'admin_categories_list', methods: ['GET'])]
    public function list(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('admin/categories/list.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/create', name: 'admin_category_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        AdminLogService $adminLogService,
    ): Response {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Generate slug from name if not provided
            if (!$category->getSlug()) {
                $slug = $slugger->slug($category->getName())->lower();
                $category->setSlug($slug);
            }

            $entityManager->persist($category);
            $entityManager->flush();

            // Log the action
            $adminLogService->logCreate($this->getUser(), 'Category', $category->getId(), [
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
            ]);

            $this->addFlash('success', 'Category created successfully.');
            return $this->redirectToRoute('admin_categories_list');
        }

        return $this->render('admin/categories/form.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(
        Category $category,
        Request $request,
        EntityManagerInterface $entityManager,
        AdminLogService $adminLogService,
    ): Response {
        $oldData = [
            'name' => $category->getName(),
            'slug' => $category->getSlug(),
        ];

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Log the action
            $adminLogService->logUpdate($this->getUser(), 'Category', $category->getId(), [
                'old' => $oldData,
                'new' => [
                    'name' => $category->getName(),
                    'slug' => $category->getSlug(),
                ],
            ]);

            $this->addFlash('success', 'Category updated successfully.');
            return $this->redirectToRoute('admin_categories_list');
        }

        return $this->render('admin/categories/form.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function delete(
        Category $category,
        EntityManagerInterface $entityManager,
        AdminLogService $adminLogService,
    ): Response {
        // Check if category has job offers
        if ($category->getJobOffers()->count() > 0) {
            $this->addFlash('error', 'Cannot delete category with associated job offers.');
            return $this->redirectToRoute('admin_categories_list');
        }

        $categoryId = $category->getId();
        $categoryName = $category->getName();

        $entityManager->remove($category);
        $entityManager->flush();

        // Log the action
        $adminLogService->logDelete($this->getUser(), 'Category', $categoryId, [
            'name' => $categoryName,
        ]);

        $this->addFlash('success', 'Category deleted successfully.');
        return $this->redirectToRoute('admin_categories_list');
    }

    #[Route('/{id}', name: 'admin_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('admin/categories/show.html.twig', [
            'category' => $category,
        ]);
    }
}

