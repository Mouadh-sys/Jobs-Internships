<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\JobOffer;
use App\Entity\User;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ApplicationService
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private string $cvDirectory,
    ) {
    }

    public function applyToOffer(
        User $candidate,
        JobOffer $offer,
        string $message,
        ?UploadedFile $cv = null,
    ): Application {
        // Check if already applied
        $existing = $this->applicationRepository->findByJobOfferAndCandidate(
            $offer->getId(),
            $candidate->getId(),
        );

        if ($existing) {
            throw new \Exception('You have already applied to this job offer');
        }

        $application = new Application();
        $application->setCandidate($candidate);
        $application->setJobOffer($offer);
        $application->setMessage($message);
        $application->setStatus(Application::STATUS_PENDING);

        // Handle CV upload
        if ($cv) {
            $filename = $this->uploadCv($cv, $candidate->getId());
            $application->setCvFilename($filename);
        }

        $this->entityManager->persist($application);
        $this->entityManager->flush();

        // Send notification email to company
        $this->sendApplicationNotificationEmail($application);

        return $application;
    }

    private function uploadCv(UploadedFile $file, int $candidateId): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = sprintf(
            '%s_%s_%s.%s',
            $candidateId,
            time(),
            $originalFilename,
            $file->guessExtension(),
        );

        $file->move($this->cvDirectory, $newFilename);

        return $newFilename;
    }

    private function sendApplicationNotificationEmail(Application $application): void
    {
        try {
            $email = (new Email())
                ->from('no-reply@jobsinternships.com')
                ->to($application->getJobOffer()->getCompany()->getUser()->getEmail())
                ->subject('New Application: ' . $application->getJobOffer()->getTitle())
                ->text('A new application has been received for the job offer: ' . $application->getJobOffer()->getTitle());

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log but don't crash if email fails
            error_log('Failed to send application notification email: ' . $e->getMessage());
        }
    }

    public function updateApplicationStatus(Application $application, string $status): void
    {
        $application->setStatus($status);
        $application->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($application);
        $this->entityManager->flush();

        $this->sendApplicationStatusEmail($application);
    }

    private function sendApplicationStatusEmail(Application $application): void
    {
        try {
            $email = (new Email())
                ->from('no-reply@jobsinternships.com')
                ->to($application->getCandidate()->getEmail())
                ->subject('Application Status: ' . ucfirst(strtolower($application->getStatus())))
                ->text('Your application status for ' . $application->getJobOffer()->getTitle() . ' has been updated to: ' . $application->getStatus());

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log but don't crash if email fails
            error_log('Failed to send application status email: ' . $e->getMessage());
        }
    }
}

