<?php

namespace App\Entity;

use App\Repository\AdminLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdminLogRepository::class)]
#[ORM\Table]
#[ORM\Index(columns: ['admin_id'])]
#[ORM\Index(columns: ['created_at'])]
#[ORM\Index(columns: ['entity_type'])]
class AdminLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'adminLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $admin = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $action = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $entityType = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $entityId = null;

    #[ORM\Column(type: 'json', nullable: true, options: ['comment' => '(DC2Type:json)'])]
    private ?array $data = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdmin(): ?User
    {
        return $this->admin;
    }

    public function setAdmin(?User $admin): static
    {
        $this->admin = $admin;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(string $entityType): static
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function setEntityId(string $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;

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
}

