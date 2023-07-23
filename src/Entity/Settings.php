<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
class Settings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $setting = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $value = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTimeInterface $modifiedon = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $defaultvalue = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSetting(): ?string
    {
        return $this->setting;
    }

    public function setSetting(string $setting): static
    {
        $this->setting = $setting;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getModifiedon(): ?\DateTimeInterface
    {
        return $this->modifiedon;
    }

    public function setModifiedon(\DateTimeInterface $modifiedon): static
    {
        $this->modifiedon = $modifiedon;

        return $this;
    }

    public function getDefaultvalue(): ?string
    {
        return $this->defaultvalue;
    }

    public function setDefaultvalue(?string $defaultvalue): static
    {
        $this->defaultvalue = $defaultvalue;

        return $this;
    }
}
