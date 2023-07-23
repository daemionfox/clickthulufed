<?php

namespace App\Entity;

use App\Repository\LayoutRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LayoutRepository::class)]
class Layout
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'layout', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Comic $comic = null;

    #[ORM\Column(length: 255)]
    private ?string $sidebarposition = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComic(): ?Comic
    {
        return $this->comic;
    }

    public function setComic(Comic $comic): static
    {
        $this->comic = $comic;

        return $this;
    }

    public function getSidebarposition(): ?string
    {
        return $this->sidebarposition;
    }

    public function setSidebarposition(string $sidebarposition): static
    {
        $this->sidebarposition = $sidebarposition;

        return $this;
    }
}
