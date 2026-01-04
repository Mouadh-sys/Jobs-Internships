<?php

namespace App\Controller\Admin;

use App\Repository\AdminLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/logs')]
#[IsGranted('ROLE_ADMIN')]
class AdminLogController extends AbstractController
{
    #[Route('', name: 'admin_logs_list', methods: ['GET'])]
    public function list(Request $request, AdminLogRepository $adminLogRepository): Response
    {
        $filters = [
            'admin' => $request->query->getInt('admin', 0),
            'entity_type' => $request->query->get('entity_type', ''),
            'action' => $request->query->get('action', ''),
            'date_from' => $request->query->get('date_from', ''),
            'date_to' => $request->query->get('date_to', ''),
        ];

        $qb = $adminLogRepository->createQueryBuilder('l')
            ->orderBy('l.createdAt', 'DESC');

        if ($filters['admin'] > 0) {
            $qb->andWhere('IDENTITY(l.admin) = :adminId')
                ->setParameter('adminId', $filters['admin']);
        }

        if (!empty($filters['entity_type'])) {
            $qb->andWhere('l.entityType = :entityType')
                ->setParameter('entityType', $filters['entity_type']);
        }

        if (!empty($filters['action'])) {
            $qb->andWhere('l.action = :action')
                ->setParameter('action', $filters['action']);
        }

        if (!empty($filters['date_from'])) {
            $qb->andWhere('l.createdAt >= :dateFrom')
                ->setParameter('dateFrom', new \DateTimeImmutable($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $qb->andWhere('l.createdAt <= :dateTo')
                ->setParameter('dateTo', new \DateTimeImmutable($filters['date_to'] . ' 23:59:59'));
        }

        $logs = $qb->getQuery()->getResult();

        return $this->render('admin/logs/list.html.twig', [
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }

    #[Route('/{id}', name: 'admin_log_show', methods: ['GET'])]
    public function show(int $id, AdminLogRepository $adminLogRepository): Response
    {
        $log = $adminLogRepository->find($id);

        if (!$log) {
            throw $this->createNotFoundException('Log entry not found');
        }

        return $this->render('admin/logs/show.html.twig', [
            'log' => $log,
        ]);
    }

    #[Route('/export', name: 'admin_logs_export', methods: ['GET'])]
    public function export(Request $request, AdminLogRepository $adminLogRepository): Response
    {
        // Build the query with filters
        $filters = [
            'admin' => $request->query->getInt('admin', 0),
            'entity_type' => $request->query->get('entity_type', ''),
            'action' => $request->query->get('action', ''),
            'date_from' => $request->query->get('date_from', ''),
            'date_to' => $request->query->get('date_to', ''),
        ];

        $qb = $adminLogRepository->createQueryBuilder('l')
            ->orderBy('l.createdAt', 'DESC');

        if ($filters['admin'] > 0) {
            $qb->andWhere('IDENTITY(l.admin) = :adminId')
                ->setParameter('adminId', $filters['admin']);
        }

        if (!empty($filters['entity_type'])) {
            $qb->andWhere('l.entityType = :entityType')
                ->setParameter('entityType', $filters['entity_type']);
        }

        if (!empty($filters['action'])) {
            $qb->andWhere('l.action = :action')
                ->setParameter('action', $filters['action']);
        }

        if (!empty($filters['date_from'])) {
            $qb->andWhere('l.createdAt >= :dateFrom')
                ->setParameter('dateFrom', new \DateTimeImmutable($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $qb->andWhere('l.createdAt <= :dateTo')
                ->setParameter('dateTo', new \DateTimeImmutable($filters['date_to'] . ' 23:59:59'));
        }

        $logs = $qb->getQuery()->getResult();

        // Create streamed CSV response
        $response = new StreamedResponse(function () use ($logs) {
            $handle = fopen('php://output', 'w+');

            // Write CSV header
            fputcsv($handle, ['ID', 'Action', 'Entity Type', 'Entity ID', 'Admin Email', 'Created At']);

            // Write data rows
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->getId(),
                    $log->getAction(),
                    $log->getEntityType(),
                    $log->getEntityId(),
                    $log->getAdmin()?->getEmail() ?? '',
                    $log->getCreatedAt()?->format('Y-m-d H:i:s') ?? '',
                ]);
            }

            fclose($handle);
        });

        $filename = 'admin-logs-' . (new \DateTime())->format('Y-m-d-His') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}

