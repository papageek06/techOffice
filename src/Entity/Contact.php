<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\Table(name: 'contact')]
#[ORM\UniqueConstraint(name: 'uniq_contact_user_source_source_id', columns: ['user_id', 'source', 'source_id'])]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 30)]
    private string $source;

    #[ORM\Column(length: 255)]
    private string $sourceId;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $givenName = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $surname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email2 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phoneMobile = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phoneBusiness = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jobTitle = null;

    /** @var array<string, mixed>|null e.g. street, city, postalCode, country */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $address = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastModifiedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;
        return $this;
    }

    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    public function setSourceId(string $sourceId): static
    {
        $this->sourceId = $sourceId;
        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): static
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): static
    {
        $this->givenName = $givenName;
        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname): static
    {
        $this->surname = $surname;
        return $this;
    }

    public function getEmail1(): ?string
    {
        return $this->email1;
    }

    public function setEmail1(?string $email1): static
    {
        $this->email1 = $email1;
        return $this;
    }

    public function getEmail2(): ?string
    {
        return $this->email2;
    }

    public function setEmail2(?string $email2): static
    {
        $this->email2 = $email2;
        return $this;
    }

    public function getPhoneMobile(): ?string
    {
        return $this->phoneMobile;
    }

    public function setPhoneMobile(?string $phoneMobile): static
    {
        $this->phoneMobile = $phoneMobile;
        return $this;
    }

    public function getPhoneBusiness(): ?string
    {
        return $this->phoneBusiness;
    }

    public function setPhoneBusiness(?string $phoneBusiness): static
    {
        $this->phoneBusiness = $phoneBusiness;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): static
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): static
    {
        $this->jobTitle = $jobTitle;
        return $this;
    }

    /** @return array<string, mixed>|null */
    public function getAddress(): ?array
    {
        return $this->address;
    }

    /** @param array<string, mixed>|null $address */
    public function setAddress(?array $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getLastModifiedAt(): ?\DateTimeImmutable
    {
        return $this->lastModifiedAt;
    }

    public function setLastModifiedAt(?\DateTimeImmutable $lastModifiedAt): static
    {
        $this->lastModifiedAt = $lastModifiedAt;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
