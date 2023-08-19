<?php

namespace App\Entity;

use App\Repository\LayoutRepository;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $css = null;

    #[ORM\Column]
    private ?bool $showInfo = true;

    #[ORM\Column]
    private ?bool $showTranscript = true;

    #[ORM\Column]
    private ?bool $showCast = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $headerimage = null;

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

    public function getCss(): ?string
    {
        return $this->css;
    }

    public function setCss(?string $css): static
    {
        $this->css = $css;

        return $this;
    }

    public function isShowInfo(): ?bool
    {
        return $this->showInfo;
    }

    public function setShowInfo(bool $showInfo): static
    {
        $this->showInfo = $showInfo;

        return $this;
    }

    public function isShowTranscript(): ?bool
    {
        return $this->showTranscript;
    }

    public function setShowTranscript(bool $showTranscript): static
    {
        $this->showTranscript = $showTranscript;

        return $this;
    }

    public function isShowCast(): ?bool
    {
        return $this->showCast;
    }

    public function setShowCast(bool $showCast): static
    {
        $this->showCast = $showCast;

        return $this;
    }

    public function getHeaderimage(): ?string
    {
        return $this->headerimage;
    }

    public function setHeaderimage(?string $headerimage): static
    {
        $this->headerimage = $headerimage;

        return $this;
    }
}
