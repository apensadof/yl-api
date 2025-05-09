<?php

namespace App\Entity;

use App\Repository\OddunRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OddunRepository::class)]
class Oddun
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $bin = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'bin' => $this->bin,
        ];
    }
} 