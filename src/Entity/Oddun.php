<?php

namespace App\Entity;

use App\Repository\OddunRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OddunRepository::class)]
#[ORM\Table(name: 'odduns')]
class Oddun
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $uid = null;

    #[ORM\Column(type: 'text')]
    private ?string $name = null;

    #[ORM\Column(type: 'text')]
    private ?string $alt_names = null;

    #[ORM\Column(type: 'text')]
    private ?string $nace = null;

    #[ORM\Column(type: 'text')]
    private ?string $frases = null;

    #[ORM\Column(type: 'text')]
    private ?string $ire = null;

    #[ORM\Column(type: 'text')]
    private ?string $osogbo = null;

    #[ORM\Column(type: 'text')]
    private ?string $bin = null;

    #[ORM\Column(type: 'text', length: 4294967295)]
    private ?string $diceifa = null;

    #[ORM\Column(type: 'text', length: 4294967295)]
    private ?string $patakies = null;

    #[ORM\Column(type: 'text')]
    private ?string $historia = null;

    #[ORM\Column(type: 'text')]
    private ?string $refranes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function setUid(int $uid): self
    {
        $this->uid = $uid;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getAltNames(): ?string
    {
        return $this->alt_names;
    }

    public function setAltNames(string $alt_names): self
    {
        $this->alt_names = $alt_names;
        return $this;
    }

    public function getNace(): ?string
    {
        return $this->nace;
    }

    public function setNace(string $nace): self
    {
        $this->nace = $nace;
        return $this;
    }

    public function getFrases(): ?string
    {
        return $this->frases;
    }

    public function setFrases(string $frases): self
    {
        $this->frases = $frases;
        return $this;
    }

    public function getIre(): ?string
    {
        return $this->ire;
    }

    public function setIre(string $ire): self
    {
        $this->ire = $ire;
        return $this;
    }

    public function getOsogbo(): ?string
    {
        return $this->osogbo;
    }

    public function setOsogbo(string $osogbo): self
    {
        $this->osogbo = $osogbo;
        return $this;
    }

    public function getBin(): ?string
    {
        return $this->bin;
    }

    public function setBin(string $bin): self
    {
        $this->bin = $bin;
        return $this;
    }

    public function getDiceifa(): ?string
    {
        return $this->diceifa;
    }

    public function setDiceifa(string $diceifa): self
    {
        $this->diceifa = $diceifa;
        return $this;
    }

    public function getPatakies(): ?string
    {
        return $this->patakies;
    }

    public function setPatakies(string $patakies): self
    {
        $this->patakies = $patakies;
        return $this;
    }

    public function getHistoria(): ?string
    {
        return $this->historia;
    }

    public function setHistoria(string $historia): self
    {
        $this->historia = $historia;
        return $this;
    }

    public function getRefranes(): ?string
    {
        return $this->refranes;
    }

    public function setRefranes(string $refranes): self
    {
        $this->refranes = $refranes;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'name' => $this->name,
            'alt_names' => $this->alt_names,
            'nace' => $this->nace,
            'frases' => $this->frases,
            'ire' => $this->ire,
            'osogbo' => $this->osogbo,
            'bin' => $this->bin,
            'diceifa' => $this->diceifa,
            'patakies' => $this->patakies,
            'historia' => $this->historia,
            'refranes' => $this->refranes,
        ];
    }
} 