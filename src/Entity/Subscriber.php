<?php

namespace App\Entity;

use App\Repository\SubscriberRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriberRepository::class)]
class Subscriber
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $subscriber = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTimeInterface $createdon = null;

    #[ORM\Column]
    private ?bool $isdeleted = false;

    public function __construct()
    {
        $this->createdon = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubscriber(): ?string
    {
        return $this->subscriber;
    }

    public function setSubscriber(string $subscriber): static
    {
        $this->subscriber = $subscriber;
        return $this;
    }

    public function getCreatedon(): ?\DateTimeInterface
    {
        return $this->createdon;
    }

    public function setCreatedon(\DateTimeInterface $createdon): static
    {
        $this->createdon = $createdon;
        return $this;
    }

    public function isIsdeleted(): ?bool
    {
        return $this->isdeleted;
    }

    public function setIsdeleted(bool $isdeleted): static
    {
        $this->isdeleted = $isdeleted;
        return $this;
    }
}
