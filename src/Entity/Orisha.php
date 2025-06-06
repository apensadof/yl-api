<?php

namespace App\Entity;

use App\Repository\OrishaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrishaRepository::class)]
#[ORM\Table(name: 'orishas')]
class Orisha
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: 'json')]
    private array $otros_nombres = [];

    #[ORM\Column(type: 'text')]
    private ?string $dominio = null;

    #[ORM\Column(length: 255)]
    private ?string $color = null;

    #[ORM\Column]
    private ?int $numero = null;

    #[ORM\Column(type: 'json')]
    private array $atributos = [];

    #[ORM\Column(length: 255)]
    private ?string $sincretismo = null;

    #[ORM\Column(length: 100)]
    private ?string $dia = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $categoria = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'orishaCabeza', targetEntity: Ahijado::class)]
    private Collection $ahijados;

    #[ORM\ManyToMany(targetEntity: Ahijado::class, mappedBy: 'orishasRecibidos')]
    private Collection $ahijadosQueLoRecibieron;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->ahijados = new ArrayCollection();
        $this->ahijadosQueLoRecibieron = new ArrayCollection();
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

    public function getOtrosNombres(): array
    {
        return $this->otros_nombres;
    }

    public function setOtrosNombres(array $otros_nombres): self
    {
        $this->otros_nombres = $otros_nombres;
        return $this;
    }

    public function getDominio(): ?string
    {
        return $this->dominio;
    }

    public function setDominio(string $dominio): self
    {
        $this->dominio = $dominio;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;
        return $this;
    }

    public function getAtributos(): array
    {
        return $this->atributos;
    }

    public function setAtributos(array $atributos): self
    {
        $this->atributos = $atributos;
        return $this;
    }

    public function getSincretismo(): ?string
    {
        return $this->sincretismo;
    }

    public function setSincretismo(string $sincretismo): self
    {
        $this->sincretismo = $sincretismo;
        return $this;
    }

    public function getDia(): ?string
    {
        return $this->dia;
    }

    public function setDia(string $dia): self
    {
        $this->dia = $dia;
        return $this;
    }

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    public function setCategoria(?string $categoria): self
    {
        $this->categoria = $categoria;
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
            $ahijado->setOrishaCabeza($this);
        }

        return $this;
    }

    public function removeAhijado(Ahijado $ahijado): self
    {
        if ($this->ahijados->removeElement($ahijado)) {
            if ($ahijado->getOrishaCabeza() === $this) {
                $ahijado->setOrishaCabeza(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ahijado>
     */
    public function getAhijadosQueLoRecibieron(): Collection
    {
        return $this->ahijadosQueLoRecibieron;
    }

    public function addAhijadoQueLoRecibio(Ahijado $ahijado): self
    {
        if (!$this->ahijadosQueLoRecibieron->contains($ahijado)) {
            $this->ahijadosQueLoRecibieron->add($ahijado);
            $ahijado->addOrishaRecibido($this);
        }

        return $this;
    }

    public function removeAhijadoQueLoRecibio(Ahijado $ahijado): self
    {
        if ($this->ahijadosQueLoRecibieron->removeElement($ahijado)) {
            $ahijado->removeOrishaRecibido($this);
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'otros_nombres' => $this->otros_nombres,
            'dominio' => $this->dominio,
            'color' => $this->color,
            'numero' => $this->numero,
            'atributos' => $this->atributos,
            'sincretismo' => $this->sincretismo,
            'dia' => $this->dia,
            'categoria' => $this->categoria,
            'created_at' => $this->created_at?->format(\DateTime::ISO8601),
            'updated_at' => $this->updated_at?->format(\DateTime::ISO8601),
        ];
    }
} 