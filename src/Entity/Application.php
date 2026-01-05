<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'UNIQ_APPLICATION_OFFER_CANDIDATE', columns: ['job_offer_id', 'candidate_id']),
])]
#[ORM\Index(columns: ['job_offer_id'])]
#[ORM\Index(columns: ['candidate_id'])]
#[ORM\Index(columns: ['status'])]
class Application
{
    final public const STATUS_PENDING = 'PENDING';
    final public const STATUS_ACCEPTED = 'ACCEPTED';
    final public const STATUS_REJECTED = 'REJECTED';
    final public const STATUS_WITHDRAWN = 'WITHDRAWN';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: JobOffer::class, inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?JobOffer $jobOffer = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $candidate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cvFilename = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->status = self::STATUS_PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobOffer(): ?JobOffer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?JobOffer $jobOffer): static
    {
        $this->jobOffer = $jobOffer;

        return $this;
    }

    public function getCandidate(): ?User
    {
        return $this->candidate;
    }

    public function setCandidate(?User $candidate): static
    {
        $this->candidate = $candidate;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getCvFilename(): ?string
    {
        return $this->cvFilename;
    }

    public function setCvFilename(?string $cvFilename): static
    {
        $this->cvFilename = $cvFilename;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, [self::STATUS_PENDING, self::STATUS_ACCEPTED, self::STATUS_REJECTED, self::STATUS_WITHDRAWN], true)) {
            throw new \InvalidArgumentException('Invalid application status');
        }
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isWithdrawn(): bool
    {
        return $this->status === self::STATUS_WITHDRAWN;
    }
}

