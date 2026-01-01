<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'UNIQ_EMAIL', columns: ['email']),
])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email]
    #[Assert\NotBlank]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cvFilename = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'user')]
    private ?Company $company = null;

    /**
     * @var Collection<int, Skill>
     */
    #[ORM\ManyToMany(targetEntity: Skill::class)]
    #[ORM\JoinTable(name: 'user_skill')]
    private Collection $skills;

    /**
     * @var Collection<int, Application>
     */
    #[ORM\OneToMany(mappedBy: 'candidate', targetEntity: Application::class, cascade: ['remove'])]
    private Collection $applications;

    /**
     * @var Collection<int, SavedOffer>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: SavedOffer::class, cascade: ['remove'])]
    private Collection $savedOffers;

    /**
     * @var Collection<int, AdminLog>
     */
    #[ORM\OneToMany(mappedBy: 'admin', targetEntity: AdminLog::class)]
    private Collection $adminLogs;

    public function __construct()
    {
        $this->skills = new ArrayCollection();
        $this->applications = new ArrayCollection();
        $this->savedOffers = new ArrayCollection();
        $this->adminLogs = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->roles = ['ROLE_USER'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): static
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        // unset the owning side of the relation if necessary
        if ($company === null && $this->company !== null) {
            $this->company->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($company !== null && $company->getUser() !== $this) {
            $company->setUser($this);
        }

        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        $this->skills->removeElement($skill);

        return $this;
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): static
    {
        if (!$this->applications->contains($application)) {
            $this->applications->add($application);
            $application->setCandidate($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): static
    {
        if ($this->applications->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getCandidate() === $this) {
                $application->setCandidate(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SavedOffer>
     */
    public function getSavedOffers(): Collection
    {
        return $this->savedOffers;
    }

    public function addSavedOffer(SavedOffer $savedOffer): static
    {
        if (!$this->savedOffers->contains($savedOffer)) {
            $this->savedOffers->add($savedOffer);
            $savedOffer->setUser($this);
        }

        return $this;
    }

    public function removeSavedOffer(SavedOffer $savedOffer): static
    {
        if ($this->savedOffers->removeElement($savedOffer)) {
            // set the owning side to null (unless already changed)
            if ($savedOffer->getUser() === $this) {
                $savedOffer->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AdminLog>
     */
    public function getAdminLogs(): Collection
    {
        return $this->adminLogs;
    }

    public function addAdminLog(AdminLog $adminLog): static
    {
        if (!$this->adminLogs->contains($adminLog)) {
            $this->adminLogs->add($adminLog);
            $adminLog->setAdmin($this);
        }

        return $this;
    }

    public function removeAdminLog(AdminLog $adminLog): static
    {
        if ($this->adminLogs->removeElement($adminLog)) {
            // set the owning side to null (unless already changed)
            if ($adminLog->getAdmin() === $this) {
                $adminLog->setAdmin(null);
            }
        }

        return $this;
    }

    public function isCompany(): bool
    {
        return in_array('ROLE_COMPANY', $this->roles, true);
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles, true);
    }
}


