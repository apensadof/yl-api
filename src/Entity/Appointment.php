<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
#[ORM\Table(name: 'appointments')]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $clientName;

    #[ORM\Column(length: 100)]
    private string $type;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $date;

    #[ORM\Column(type: 'time')]
    private \DateTimeInterface $time;

    #[ORM\Column(type: 'integer')]
    private int $duration; // in minutes

    #[ORM\Column(length: 20, options: ['default' => 'pendiente'])]
    private string $status = 'pendiente';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }

    public function setClientName(string $clientName): static
    {
        $this->clientName = $clientName;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $allowedTypes = ['consulta', 'ebbo', 'iniciacion', 'limpieza', 'ceremonia', 'Consulta de Ifá', 'Consulta Rápida', 'Limpieza Espiritual'];
        // Allow backwards compatibility with existing types
        $this->type = $type;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getGeneratedTitle(): string
    {
        if ($this->title) {
            return $this->title;
        }
        
        $typeNames = [
            'consulta' => 'Consulta de Ifá',
            'ebbo' => 'Ebbó',
            'iniciacion' => 'Iniciación',
            'limpieza' => 'Limpieza Espiritual',
            'ceremonia' => 'Ceremonia'
        ];
        
        $typeName = $typeNames[strtolower($this->type)] ?? $this->type;
        return $typeName . ' - ' . $this->clientName;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getTime(): \DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): static
    {
        $this->time = $time;
        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, ['pendiente', 'confirmada', 'completada', 'cancelada'])) {
            throw new \InvalidArgumentException('Status inválido');
        }
        $this->status = $status;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
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

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function isToday(): bool
    {
        $today = new \DateTime();
        return $this->date->format('Y-m-d') === $today->format('Y-m-d');
    }

    public function getDateTime(): \DateTime
    {
        $datetime = new \DateTime($this->date->format('Y-m-d'));
        $time = explode(':', $this->time->format('H:i'));
        $datetime->setTime((int)$time[0], (int)$time[1]);
        return $datetime;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getGeneratedTitle(),
            'clientName' => $this->clientName,
            'type' => $this->type,
            'date' => $this->date->format('Y-m-d'),
            'time' => $this->time->format('H:i'),
            'duration' => $this->duration,
            'status' => $this->status,
            'notes' => $this->notes,
            'createdAt' => $this->createdAt->format('c'),
            'updatedAt' => $this->updatedAt->format('c')
        ];
    }

    public function toCalendarArray(): array
    {
        return [
            'id' => $this->id,
            'clientName' => $this->clientName,
            'type' => $this->type,
            'date' => $this->date->format('Y-m-d'),
            'time' => $this->time->format('H:i'),
            'duration' => $this->duration,
            'status' => $this->status
        ];
    }
} 