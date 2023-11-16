<?php

namespace App\Entity;

use App\Helpers\CryptoHelper;
use App\Repository\CryptKeyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\ParameterBag;

#[ORM\Entity(repositoryClass: CryptKeyRepository::class)]
class CryptKey extends CryptoHelper
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $data = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTimeInterface $createdon = null;

    public function __construct()
    {
        parent::__construct();
        $this->createdon = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData()
    {
        return $this->decrypt($this->data);
    }

    public function setData($data): static
    {
        $this->data = $this->encrypt($data);
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

}
