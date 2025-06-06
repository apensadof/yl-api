<?php

namespace App\Entity;

use App\Repository\OddunsOldRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OddunsOldRepository::class)]
#[ORM\Table(name: 'odduns_old')]
class OddunsOld
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
    private ?string $nace = null;

    #[ORM\Column(type: 'text')]
    private ?string $refr = null;

    #[ORM\Column(type: 'text', length: 4294967295)]
    private ?string $dic = null;

    #[ORM\Column(type: 'text')]
    private ?string $bin = null;

    #[ORM\Column(type: 'text', length: 4294967295)]
    private ?string $patakins = null;

    #[ORM\Column(type: 'text', length: 4294967295, nullable: true)]
    private ?string $resumen = null;

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

    public function getNace(): ?string
    {
        return $this->nace;
    }

    public function setNace(string $nace): self
    {
        $this->nace = $nace;
        return $this;
    }

    public function getRefr(): ?string
    {
        return $this->refr;
    }

    public function setRefr(string $refr): self
    {
        $this->refr = $refr;
        return $this;
    }

    public function getDic(): ?string
    {
        return $this->dic;
    }

    public function setDic(string $dic): self
    {
        $this->dic = $dic;
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

    public function getPatakins(): ?string
    {
        return $this->patakins;
    }

    public function setPatakins(string $patakins): self
    {
        $this->patakins = $patakins;
        return $this;
    }

    public function getResumen(): ?string
    {
        return $this->resumen;
    }

    public function setResumen(?string $resumen): self
    {
        $this->resumen = $resumen;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'name' => $this->name,
            'nace' => $this->nace,
            'refr' => $this->refr,
            'dic' => $this->dic,
            'bin' => $this->bin,
            'patakins' => $this->patakins,
            'resumen' => $this->resumen,
        ];
    }
} 