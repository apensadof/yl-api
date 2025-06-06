<?php

namespace App\Entity;

use App\Repository\AhijadoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AhijadoRepository::class)]
#[ORM\Table(name: 'ahijados')]
class Ahijado
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $initiation_date = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\ManyToOne(targetEntity: Orisha::class, inversedBy: 'ahijados')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Orisha $orishaCabeza = null;

    #[ORM\ManyToMany(targetEntity: Orisha::class, inversedBy: 'ahijadosQueLoRecibieron')]
    #[ORM\JoinTable(name: 'ahijado_orisha')]
    private Collection $orishasRecibidos;

    #[ORM\ManyToMany(targetEntity: Ceremonia::class, inversedBy: 'ahijados')]
    #[ORM\JoinTable(name: 'ahijado_ceremonia')]
    private Collection $ceremoniasRealizadas;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->orishasRecibidos = new ArrayCollection();
        $this->ceremoniasRealizadas = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->updated_at = new \DateTime();
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        $this->updated_at = new \DateTime();
        return $this;
    }

    public function getInitiationDate(): ?\DateTimeInterface
    {
        return $this->initiation_date;
    }

    public function setInitiationDate(?\DateTimeInterface $initiation_date): self
    {
        $this->initiation_date = $initiation_date;
        $this->updated_at = new \DateTime();
        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;
        $this->updated_at = new \DateTime();
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        $this->updated_at = new \DateTime();
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        $this->updated_at = new \DateTime();
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
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

    public function getOrishaCabeza(): ?Orisha
    {
        return $this->orishaCabeza;
    }

    public function setOrishaCabeza(?Orisha $orishaCabeza): self
    {
        $this->orishaCabeza = $orishaCabeza;
        return $this;
    }

    /**
     * @return Collection<int, Orisha>
     */
    public function getOrishasRecibidos(): Collection
    {
        return $this->orishasRecibidos;
    }

    public function addOrishaRecibido(Orisha $orisha): self
    {
        if (!$this->orishasRecibidos->contains($orisha)) {
            $this->orishasRecibidos->add($orisha);
        }

        return $this;
    }

    public function removeOrishaRecibido(Orisha $orisha): self
    {
        $this->orishasRecibidos->removeElement($orisha);

        return $this;
    }

    /**
     * @return Collection<int, Ceremonia>
     */
    public function getCeremoniasRealizadas(): Collection
    {
        return $this->ceremoniasRealizadas;
    }

    public function addCeremoniaRealizada(Ceremonia $ceremonia): self
    {
        if (!$this->ceremoniasRealizadas->contains($ceremonia)) {
            $this->ceremoniasRealizadas->add($ceremonia);
        }

        return $this;
    }

    public function removeCeremoniaRealizada(Ceremonia $ceremonia): self
    {
        $this->ceremoniasRealizadas->removeElement($ceremonia);

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'initiationDate' => $this->initiation_date?->format('Y-m-d'),
            'birthdate' => $this->birthdate?->format('Y-m-d'),
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'notes' => $this->notes,
            'orishaCabeza' => $this->orishaCabeza?->toArray(),
            'orishasRecibidos' => array_map(fn($orisha) => $orisha->toArray(), $this->orishasRecibidos->toArray()),
            'ceremoniasRealizadas' => array_map(fn($ceremonia) => $ceremonia->toArray(), $this->ceremoniasRealizadas->toArray()),
            'createdAt' => $this->created_at?->format(\DateTime::ISO8601),
            'updatedAt' => $this->updated_at?->format(\DateTime::ISO8601),
        ];
    }
} 