<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $tag = null;

    #[ORM\ManyToMany(targetEntity: Comic::class, inversedBy: 'tags')]
    private Collection $comic;

    public function __construct()
    {
        $this->comic = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return Collection<int, Comic>
     */
    public function getComic(): Collection
    {
        return $this->comic;
    }

    public function addComic(Comic $comic): static
    {
        if (!$this->comic->contains($comic)) {
            $this->comic->add($comic);
        }

        return $this;
    }

    public function removeComic(Comic $comic): static
    {
        $this->comic->removeElement($comic);

        return $this;
    }
}
