<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Comic $comic = null;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    private ?Chapter $chapter = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $info = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTimeInterface $publishdate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $transcript = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getComic(): ?Comic
    {
        return $this->comic;
    }

    public function setComic(?Comic $comic): static
    {
        $this->comic = $comic;

        return $this;
    }

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function setChapter(?Chapter $chapter): static
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getBlog(): ?string
    {
        return $this->blog;
    }

    public function setBlog(?string $blog): static
    {
        $this->blog = $blog;

        return $this;
    }

    public function getPublishdate(): ?\DateTimeInterface
    {
        return $this->publishdate;
    }

    public function setPublishdate(\DateTimeInterface $publishdate): static
    {
        $this->publishdate = $publishdate;

        return $this;
    }

    public function getTranscript(): ?string
    {
        return $this->transcript;
    }

    public function setTranscript(string $transcript): static
    {
        $this->transcript = $transcript;

        return $this;
    }
}
