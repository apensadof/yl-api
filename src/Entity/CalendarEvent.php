<?php

namespace App\Entity;

use App\Repository\CalendarEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CalendarEventRepository::class)]
#[ORM\Table(name: 'calendar_events')]
class CalendarEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $client = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 5)]
    private ?string $time = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

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

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(string $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(string $time): self
    {
        $this->time = $time;
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
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

    public function getTitle(): string
    {
        $typeNames = [
            'consulta' => 'Consulta de Ifá',
            'ebbo' => 'Ebbó',
            'iniciacion' => 'Iniciación',
            'limpieza' => 'Limpieza Espiritual',
            'ceremonia' => 'Ceremonia'
        ];

        $typeName = $typeNames[$this->type] ?? ucfirst($this->type);
        return $typeName . ' - ' . $this->client;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTitle(),
            'client' => $this->client,
            'type' => $this->type,
            'date' => $this->date?->format('Y-m-d'),
            'time' => $this->time,
            'duration' => $this->duration,
            'notes' => $this->notes,
            'createdAt' => $this->created_at?->format(\DateTime::ISO8601),
            'updatedAt' => $this->updated_at?->format(\DateTime::ISO8601),
        ];
    }

    public static function getEventTypes(): array
    {
        return [
            [
                'id' => 'consulta',
                'name' => 'Consulta de Ifá',
                'color' => '#2E8B57',
                'defaultDuration' => 60,
                'description' => 'Consulta espiritual completa con Ifá'
            ],
            [
                'id' => 'ebbo',
                'name' => 'Ebbó',
                'color' => '#F5A623',
                'defaultDuration' => 120,
                'description' => 'Ceremonia de limpieza y purificación'
            ],
            [
                'id' => 'iniciacion',
                'name' => 'Iniciación',
                'color' => '#8B5CF6',
                'defaultDuration' => 480,
                'description' => 'Ceremonia de iniciación espiritual'
            ],
            [
                'id' => 'limpieza',
                'name' => 'Limpieza Espiritual',
                'color' => '#06B6D4',
                'defaultDuration' => 90,
                'description' => 'Sesión de limpieza energética'
            ],
            [
                'id' => 'ceremonia',
                'name' => 'Ceremonia',
                'color' => '#DC2626',
                'defaultDuration' => 180,
                'description' => 'Ceremonia espiritual general'
            ]
        ];
    }

    public static function getValidEventTypes(): array
    {
        return array_column(self::getEventTypes(), 'id');
    }
} 