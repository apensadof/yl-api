<?php

namespace App\Entity;

use App\Repository\KnowledgeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KnowledgeRepository::class)]
#[ORM\Table(name: 'knowledge_items')]
class Knowledge
{
    #[ORM\Id]
    #[ORM\Column(length: 100)]
    private string $id;

    #[ORM\Column(length: 200)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'json')]
    private array $keywords = [];

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $views = 0;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    #[ORM\ManyToOne(targetEntity: KnowledgeCategory::class, inversedBy: 'knowledgeItems')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false)]
    private ?KnowledgeCategory $category = null;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'relatedFrom')]
    #[ORM\JoinTable(name: 'knowledge_related',
        joinColumns: [new ORM\JoinColumn(name: 'knowledge_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'related_id', referencedColumnName: 'id')]
    )]
    private Collection $relatedTo;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'relatedTo')]
    private Collection $relatedFrom;

    public function __construct()
    {
        $this->relatedTo = new ArrayCollection();
        $this->relatedFrom = new ArrayCollection();
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function setKeywords(array $keywords): static
    {
        $this->keywords = $keywords;
        return $this;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): static
    {
        $this->views = $views;
        return $this;
    }

    public function incrementViews(): static
    {
        $this->views++;
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

    public function getCategory(): ?KnowledgeCategory
    {
        return $this->category;
    }

    public function setCategory(?KnowledgeCategory $category): static
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getRelatedTo(): Collection
    {
        return $this->relatedTo;
    }

    public function addRelatedTo(self $relatedTo): static
    {
        if (!$this->relatedTo->contains($relatedTo)) {
            $this->relatedTo->add($relatedTo);
        }

        return $this;
    }

    public function removeRelatedTo(self $relatedTo): static
    {
        $this->relatedTo->removeElement($relatedTo);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getRelatedFrom(): Collection
    {
        return $this->relatedFrom;
    }

    public function addRelatedFrom(self $relatedFrom): static
    {
        if (!$this->relatedFrom->contains($relatedFrom)) {
            $this->relatedFrom->add($relatedFrom);
            $relatedFrom->addRelatedTo($this);
        }

        return $this;
    }

    public function removeRelatedFrom(self $relatedFrom): static
    {
        if ($this->relatedFrom->removeElement($relatedFrom)) {
            $relatedFrom->removeRelatedTo($this);
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
            'title' => $this->title,
            'category' => $this->category?->getId(),
            'content' => $this->content,
            'keywords' => $this->keywords,
            'views' => $this->views,
            'createdAt' => $this->createdAt->format('c'),
            'updatedAt' => $this->updatedAt->format('c')
        ];
    }

    public function toSearchResult(float $relevance = 1.0): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category?->getId(),
            'content' => substr($this->content, 0, 200) . '...',
            'keywords' => $this->keywords,
            'relevance' => $relevance
        ];
    }

    public function toDetailedArray(): array
    {
        $relatedItems = [];
        foreach ($this->relatedTo as $related) {
            $relatedItems[] = [
                'id' => $related->getId(),
                'title' => $related->getTitle(),
                'category' => $related->getCategory()?->getId()
            ];
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category?->getId(),
            'content' => $this->content,
            'keywords' => $this->keywords,
            'relatedItems' => $relatedItems,
            'createdAt' => $this->createdAt->format('c'),
            'updatedAt' => $this->updatedAt->format('c'),
            'views' => $this->views
        ];
    }
} 