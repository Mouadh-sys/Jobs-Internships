<?php

namespace App\Controller\Admin;

use App\Repository\AdminLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/logs')]
#[IsGranted('ROLE_ADMIN')]
class AdminLogController extends AbstractController
{
    #[Route('', name: 'admin_logs_list', methods: ['GET'])]
    public function list(AdminLogRepository $adminLogRepository): Response
    {
        // TODO: List admin logs with pagination
        // - Filter by admin user
        // - Filter by entity type
        // - Filter by action
        // - Date range filter

        return $this->render('admin/logs/list.html.twig', [
            // TODO: Pass logs
        ]);
    }

    #[Route('/{id}', name: 'admin_log_show', methods: ['GET'])]
    public function show(int $id, AdminLogRepository $adminLogRepository): Response
    {
        // TODO: Show log entry details

        return $this->render('admin/logs/show.html.twig', [
            // TODO: Pass log
        ]);
    }

    #[Route('/export', name: 'admin_logs_export', methods: ['GET'])]
    public function export(AdminLogRepository $adminLogRepository): Response
    {
        // TODO: Export logs as CSV

        return $this->json(['message' => 'Export not implemented']);
    }
}

