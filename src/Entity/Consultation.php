<?php

namespace App\Entity;

use App\Repository\ConsultationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultationRepository::class)]
#[ORM\Table(name: 'consultations')]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $client_name = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\Column(type: 'json')]
    private array $signs = [];

    #[ORM\Column(length: 10)]
    private ?string $ire_osogbo = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updated_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->client_name;
    }

    public function setClientName(string $client_name): self
    {
        $this->client_name = $client_name;
        $this->updated_at = new \DateTime();
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        $this->updated_at = new \DateTime();
        return $this;
    }

    public function getSigns(): array
    {
        return $this->signs;
    }

    public function setSigns(array $signs): self
    {
        $this->signs = $signs;
        $this->updated_at = new \DateTime();
        return $this;
    }

    public function getIreOsogbo(): ?string
    {
        return $this->ire_osogbo;
    }

    public function setIreOsogbo(string $ire_osogbo): self
    {
        $this->ire_osogbo = $ire_osogbo;
        $this->updated_at = new \DateTime();
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        $this->updated_at = new \DateTime();
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        $this->updated_at = new \DateTime();
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'clientName' => $this->client_name,
            'type' => $this->type,
            'signs' => $this->signs,
            'ire_osogbo' => $this->ire_osogbo,
            'notes' => $this->notes,
            'date' => $this->date?->format(\DateTime::ISO8601),
            'createdAt' => $this->created_at?->format(\DateTime::ISO8601),
            'updatedAt' => $this->updated_at?->format(\DateTime::ISO8601),
        ];
    }
} 