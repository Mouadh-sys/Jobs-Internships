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
    public function list(Request $request, AdminLogRepository $adminLogRepository): Response
    {
        $adminId = $request->query->get('admin');
        $entityType = $request->query->get('entity_type');
        $action = $request->query->get('action');
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');

        $criteria = [];
        if ($adminId) {
            $criteria['admin'] = $adminId;
        }
        if ($entityType) {
            $criteria['entityType'] = $entityType;
        }
        if ($action) {
            $criteria['action'] = $action;
        }

        $logs = $adminLogRepository->findBy($criteria, ['createdAt' => 'DESC']);

        // Filter by date range if provided
        if ($dateFrom || $dateTo) {
            $logs = array_filter($logs, function($log) use ($dateFrom, $dateTo) {
                $createdAt = $log->getCreatedAt();
                if ($dateFrom && $createdAt < new \DateTimeImmutable($dateFrom)) {
                    return false;
                }
                if ($dateTo && $createdAt > new \DateTimeImmutable($dateTo)) {
                    return false;
                }
                return true;
            });
        }

        return $this->render('admin/logs/list.html.twig', [
            'logs' => $logs,
            'filters' => [
                'admin' => $adminId,
                'entity_type' => $entityType,
                'action' => $action,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    #[Route('/{id}', name: 'admin_log_show', methods: ['GET'])]
    public function show(int $id, AdminLogRepository $adminLogRepository): Response
    {
        $log = $adminLogRepository->find($id);

        if (!$log) {
            throw $this->createNotFoundException('Log not found');
        }

        return $this->render('admin/logs/show.html.twig', [
            'log' => $log,
        ]);
    }

    #[Route('/export', name: 'admin_logs_export', methods: ['GET'])]
    public function export(Request $request, AdminLogRepository $adminLogRepository): Response
    {
        $adminId = $request->query->get('admin');
        $entityType = $request->query->get('entity_type');
        $action = $request->query->get('action');

        $criteria = [];
        if ($adminId) {
            $criteria['admin'] = $adminId;
        }
        if ($entityType) {
            $criteria['entityType'] = $entityType;
        }
        if ($action) {
            $criteria['action'] = $action;
        }

        $logs = $adminLogRepository->findBy($criteria, ['createdAt' => 'DESC']);

        $csv = "ID,Admin,Action,Entity Type,Entity ID,Data,Created At\n";
        foreach ($logs as $log) {
            $adminEmail = $log->getAdmin() ? $log->getAdmin()->getEmail() : 'N/A';
            $data = $log->getData() ? json_encode($log->getData()) : '';
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s\n",
                $log->getId(),
                $adminEmail,
                $log->getAction(),
                $log->getEntityType(),
                $log->getEntityId(),
                $data,
                $log->getCreatedAt()->format('Y-m-d H:i:s')
            );
        }

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="admin_logs_' . date('Y-m-d') . '.csv"');

        return $response;
    }
}

