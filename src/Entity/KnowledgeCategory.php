<?php

namespace App\Entity;

use App\Repository\KnowledgeCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KnowledgeCategoryRepository::class)]
#[ORM\Table(name: 'knowledge_categories')]
class KnowledgeCategory
{
    #[ORM\Id]
    #[ORM\Column(length: 50)]
    private string $id;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $itemCount = 0;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Knowledge::class)]
    private Collection $knowledgeItems;

    public function __construct()
    {
        $this->knowledgeItems = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getItemCount(): int
    {
        return $this->itemCount;
    }

    public function setItemCount(int $itemCount): static
    {
        $this->itemCount = $itemCount;
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

    /**
     * @return Collection<int, Knowledge>
     */
    public function getKnowledgeItems(): Collection
    {
        return $this->knowledgeItems;
    }

    public function addKnowledgeItem(Knowledge $knowledgeItem): static
    {
        if (!$this->knowledgeItems->contains($knowledgeItem)) {
            $this->knowledgeItems->add($knowledgeItem);
            $knowledgeItem->setCategory($this);
        }

        return $this;
    }

    public function removeKnowledgeItem(Knowledge $knowledgeItem): static
    {
        if ($this->knowledgeItems->removeElement($knowledgeItem)) {
            if ($knowledgeItem->getCategory() === $this) {
                $knowledgeItem->setCategory(null);
            }
        }

        return $this;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'count' => $this->itemCount
        ];
    }
} 