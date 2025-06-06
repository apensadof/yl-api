<?php

namespace App\Entity;

use App\Repository\PasosAwoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PasosAwoRepository::class)]
#[ORM\Table(name: 'pasos_awo')]
class PasosAwo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private ?string $uid = null;

    #[ORM\Column(type: 'text')]
    private ?string $titulo = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $padre = null;

    #[ORM\Column(type: 'text', length: 4294967295)]
    private ?string $contenido = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date_added = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date_updated = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;
        return $this;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getPadre(): ?string
    {
        return $this->padre;
    }

    public function setPadre(?string $padre): self
    {
        $this->padre = $padre;
        return $this;
    }

    public function getContenido(): ?string
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido): self
    {
        $this->contenido = $contenido;
        return $this;
    }

    public function getDateAdded(): ?\DateTimeInterface
    {
        return $this->date_added;
    }

    public function setDateAdded(\DateTimeInterface $date_added): self
    {
        $this->date_added = $date_added;
        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface
    {
        return $this->date_updated;
    }

    public function setDateUpdated(\DateTimeInterface $date_updated): self
    {
        $this->date_updated = $date_updated;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'titulo' => $this->titulo,
            'padre' => $this->padre,
            'contenido' => $this->contenido,
            'date_added' => $this->date_added?->format('Y-m-d H:i:s'),
            'date_updated' => $this->date_updated?->format('Y-m-d H:i:s'),
        ];
    }
} 