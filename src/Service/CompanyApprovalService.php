<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class CompanyApprovalService
{
    public function __construct(
        private AdminLogService $adminLogService,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
    ) {
    }

    public function approve(Company $company, User $admin): void
    {
        $company->setApproved(true);
        $company->setActive(true);

        $this->entityManager->persist($company);
        $this->entityManager->flush();

        // Log the action
        $this->adminLogService->logApprove($admin, 'Company', $company->getId());

        // Send approval email
        $this->sendApprovalEmail($company);
    }

    public function reject(Company $company, User $admin, string $reason = ''): void
    {
        $company->setApproved(false);
        $company->setActive(false);

        $this->entityManager->persist($company);
        $this->entityManager->flush();

        // Log the action
        $data = [];
        if ($reason) {
            $data = ['reason' => $reason];
        }
        $this->adminLogService->logReject($admin, 'Company', $company->getId(), $data);

        // Send rejection email
        $this->sendRejectionEmail($company, $reason);
    }

    private function sendApprovalEmail(Company $company): void
    {
        try {
            $email = (new Email())
                ->from('no-reply@jobsinternships.com')
                ->to($company->getUser()->getEmail())
                ->subject('Your Company Has Been Approved')
                ->text('Your company account has been approved and is now active.');

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log but don't crash if email fails
            error_log('Failed to send approval email to ' . $company->getUser()->getEmail() . ': ' . $e->getMessage());
        }
    }

    private function sendRejectionEmail(Company $company, string $reason = ''): void
    {
        try {
            $message = 'Your company account has been rejected';
            if ($reason) {
                $message .= '. Reason: ' . $reason;
            }

            $email = (new Email())
                ->from('no-reply@jobsinternships.com')
                ->to($company->getUser()->getEmail())
                ->subject('Your Company Account Has Been Rejected')
                ->text($message);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log but don't crash if email fails
            error_log('Failed to send rejection email to ' . $company->getUser()->getEmail() . ': ' . $e->getMessage());
        }
    }
}

