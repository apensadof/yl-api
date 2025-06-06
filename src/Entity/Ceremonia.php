<?php

namespace App\Entity;

use App\Repository\CeremoniaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CeremoniaRepository::class)]
#[ORM\Table(name: 'ceremonias')]
class Ceremonia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: 'text')]
    private ?string $descripcion = null;

    #[ORM\Column(length: 100)]
    private ?string $categoria = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $requisitos = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $materiales = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $procedimiento = null;

    #[ORM\Column(nullable: true)]
    private ?int $duracion_minutos = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\ManyToMany(targetEntity: Ahijado::class, mappedBy: 'ceremoniasRealizadas')]
    private Collection $ahijados;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->ahijados = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    public function setCategoria(string $categoria): self
    {
        $this->categoria = $categoria;
        return $this;
    }

    public function getRequisitos(): ?string
    {
        return $this->requisitos;
    }

    public function setRequisitos(?string $requisitos): self
    {
        $this->requisitos = $requisitos;
        return $this;
    }

    public function getMateriales(): array
    {
        return $this->materiales;
    }

    public function setMateriales(?array $materiales): self
    {
        $this->materiales = $materiales ?? [];
        return $this;
    }

    public function getProcedimiento(): ?string
    {
        return $this->procedimiento;
    }

    public function setProcedimiento(?string $procedimiento): self
    {
        $this->procedimiento = $procedimiento;
        return $this;
    }

    public function getDuracionMinutos(): ?int
    {
        return $this->duracion_minutos;
    }

    public function setDuracionMinutos(?int $duracion_minutos): self
    {
        $this->duracion_minutos = $duracion_minutos;
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

    /**
     * @return Collection<int, Ahijado>
     */
    public function getAhijados(): Collection
    {
        return $this->ahijados;
    }

    public function addAhijado(Ahijado $ahijado): self
    {
        if (!$this->ahijados->contains($ahijado)) {
            $this->ahijados->add($ahijado);
            $ahijado->addCeremoniaRealizada($this);
        }

        return $this;
    }

    public function removeAhijado(Ahijado $ahijado): self
    {
        if ($this->ahijados->removeElement($ahijado)) {
            $ahijado->removeCeremoniaRealizada($this);
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'categoria' => $this->categoria,
            'requisitos' => $this->requisitos,
            'materiales' => $this->materiales,
            'procedimiento' => $this->procedimiento,
            'duracion_minutos' => $this->duracion_minutos,
            'created_at' => $this->created_at?->format(\DateTime::ISO8601),
            'updated_at' => $this->updated_at?->format(\DateTime::ISO8601),
        ];
    }
} 