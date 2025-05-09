<?php

namespace App\Entity;

use App\Repository\ComplementoOddunRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComplementoOddunRepository::class)]
class ComplementoOddun
{
    #[ORM\Id]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $principios_metafisicos = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $resumen_osode = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $rezos = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $proverbios_totem_dualidad = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $patakis = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getPrincipiosMetafisicos(): ?string
    {
        return $this->principios_metafisicos;
    }

    public function setPrincipiosMetafisicos(?string $principios_metafisicos): self
    {
        $this->principios_metafisicos = $principios_metafisicos;
        return $this;
    }

    public function getResumenOsode(): ?string
    {
        return $this->resumen_osode;
    }

    public function setResumenOsode(?string $resumen_osode): self
    {
        $this->resumen_osode = $resumen_osode;
        return $this;
    }

    public function getRezos(): ?string
    {
        return $this->rezos;
    }

    public function setRezos(?string $rezos): self
    {
        $this->rezos = $rezos;
        return $this;
    }

    public function getProverbiosTotemDualidad(): ?string
    {
        return $this->proverbios_totem_dualidad;
    }

    public function setProverbiosTotemDualidad(?string $proverbios_totem_dualidad): self
    {
        $this->proverbios_totem_dualidad = $proverbios_totem_dualidad;
        return $this;
    }

    public function getPatakis(): ?string
    {
        return $this->patakis;
    }

    public function setPatakis(?string $patakis): self
    {
        $this->patakis = $patakis;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'principios_metafisicos' => $this->principios_metafisicos,
            'resumen_osode' => $this->resumen_osode,
            'rezos' => $this->rezos,
            'proverbios_totem_dualidad' => $this->proverbios_totem_dualidad,
            'patakis' => $this->patakis,
        ];
    }
} 