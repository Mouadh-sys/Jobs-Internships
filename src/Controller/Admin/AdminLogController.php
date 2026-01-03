<?php

namespace App\Controller\Admin;

use App\Repository\AdminLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
            $qb->andWhere('l.admin = :admin')
                ->setParameter('admin', $filters['admin']);
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
    public function export(AdminLogRepository $adminLogRepository): Response
    {
        // TODO: Export logs as CSV

        return $this->json(['message' => 'Export not implemented']);
    }
}

