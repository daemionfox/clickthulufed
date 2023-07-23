<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $sunday = false;

    #[ORM\Column]
    private ?bool $monday = false;

    #[ORM\Column]
    private ?bool $tuesday = false;

    #[ORM\Column]
    private ?bool $wednesday = false;

    #[ORM\Column]
    private ?bool $thursday = false;

    #[ORM\Column]
    private ?bool $friday = false;

    #[ORM\Column]
    private ?bool $saturday = false;

    #[ORM\OneToOne(inversedBy: 'schedule', cascade: ['persist', 'remove'])]
    private ?Comic $comic = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $time = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $timezone = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function isSunday(): ?bool
    {
        return $this->sunday;
    }

    public function setSunday(bool $sunday): static
    {
        $this->sunday = $sunday;

        return $this;
    }

    public function isMonday(): ?bool
    {
        return $this->monday;
    }

    public function setMonday(bool $monday): static
    {
        $this->monday = $monday;

        return $this;
    }

    public function isTuesday(): ?bool
    {
        return $this->tuesday;
    }

    public function setTuesday(bool $tuesday): static
    {
        $this->tuesday = $tuesday;

        return $this;
    }

    public function isWednesday(): ?bool
    {
        return $this->wednesday;
    }

    public function setWednesday(bool $wednesday): static
    {
        $this->wednesday = $wednesday;

        return $this;
    }

    public function isThursday(): ?bool
    {
        return $this->thursday;
    }

    public function setThursday(bool $thursday): static
    {
        $this->thursday = $thursday;

        return $this;
    }

    public function isFriday(): ?bool
    {
        return $this->friday;
    }

    public function setFriday(bool $friday): static
    {
        $this->friday = $friday;

        return $this;
    }

    public function isSaturday(): ?bool
    {
        return $this->saturday;
    }

    public function setSaturday(bool $saturday): static
    {
        $this->saturday = $saturday;

        return $this;
    }

    public function getHours(): ?int
    {
        return $this->hours;
    }

    public function setHours(int $hours): static
    {
        $this->hours = $hours;

        return $this;
    }

    public function getMinutes(): ?int
    {
        return $this->minutes;
    }

    public function setMinutes(int $minutes): static
    {
        $this->minutes = $minutes;

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

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(?\DateTimeInterface $time): static
    {
        $this->time = $time;

        return $this;
    }
}
