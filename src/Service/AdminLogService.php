<?php

namespace App\Service;

use App\Entity\AdminLog;
use App\Entity\User;
use App\Repository\AdminLogRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminLogService
{
    public function __construct(
        private AdminLogRepository $adminLogRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function log(
        User $admin,
        string $action,
        string $entityType,
        string $entityId,
        array $data = [],
    ): AdminLog {
        $log = new AdminLog();
        $log->setAdmin($admin);
        $log->setAction($action);
        $log->setEntityType($entityType);
        $log->setEntityId($entityId);

        if (!empty($data)) {
            $log->setData($data);
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return $log;
    }

    public function logCreate(User $admin, string $entityType, int $entityId, array $data = []): AdminLog
    {
        return $this->log($admin, 'CREATE', $entityType, (string) $entityId, $data);
    }

    public function logUpdate(User $admin, string $entityType, int $entityId, array $data = []): AdminLog
    {
        return $this->log($admin, 'UPDATE', $entityType, (string) $entityId, $data);
    }

    public function logDelete(User $admin, string $entityType, int $entityId, array $data = []): AdminLog
    {
        return $this->log($admin, 'DELETE', $entityType, (string) $entityId, $data);
    }

    public function logApprove(User $admin, string $entityType, int $entityId): AdminLog
    {
        return $this->log($admin, 'APPROVE', $entityType, (string) $entityId);
    }

    public function logReject(User $admin, string $entityType, int $entityId, array $data = []): AdminLog
    {
        return $this->log($admin, 'REJECT', $entityType, (string) $entityId, $data);
    }
}

