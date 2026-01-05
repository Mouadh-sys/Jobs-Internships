<?php

namespace App\Controller\Admin;

use App\Entity\Skill;
use App\Form\SkillType;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/skills')]
#[IsGranted('ROLE_ADMIN')]
class SkillController extends AbstractController
{
    #[Route('', name: 'admin_skill_index', methods: ['GET'])]
    public function index(SkillRepository $skillRepository): Response
    {
        return $this->render('admin/skill/index.html.twig', [
            'skills' => $skillRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_skill_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $skill = new Skill();
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $skill->setSlug(strtolower($slugger->slug($skill->getName())));
            $entityManager->persist($skill);
            $entityManager->flush();

            $this->addFlash('success', 'Skill created successfully!');
            return $this->redirectToRoute('admin_skill_index');
        }

        return $this->render('admin/skill/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_skill_edit', methods: ['GET', 'POST'])]
    public function edit(Skill $skill, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $skill->setSlug(strtolower($slugger->slug($skill->getName())));
            $entityManager->flush();

            $this->addFlash('success', 'Skill updated successfully!');
            return $this->redirectToRoute('admin_skill_index');
        }

        return $this->render('admin/skill/edit.html.twig', [
            'form' => $form,
            'skill' => $skill,
        ]);
    }

    #[Route('/{id}', name: 'admin_skill_delete', methods: ['POST'])]
    public function delete(Skill $skill, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$skill->getId(), $request->request->get('_token'))) {
            $entityManager->remove($skill);
            $entityManager->flush();
            $this->addFlash('success', 'Skill deleted successfully!');
        }

        return $this->redirectToRoute('admin_skill_index');
    }
}

