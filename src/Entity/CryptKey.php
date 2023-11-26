<?php

namespace App\Entity;

use App\Enumerations\EncryptedDataException;
use App\Repository\CryptKeyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CryptKeyRepository::class)]
class CryptKey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private $data = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTimeInterface $createdon = null;


    /**
     *
     */
    public function __construct()
    {
        $this->createdon = new \DateTime();

    }

    /**
     * @param string $piikey
     * @return $this
     */
    public function setPIIKey(string $piikey): static
    {
        $this->piikey = $piikey;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setData($data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedon(): ?\DateTimeInterface
    {
        return $this->createdon;
    }

    /**
     * @param \DateTimeInterface $createdon
     * @return $this
     */
    public function setCreatedon(\DateTimeInterface $createdon): static
    {
        $this->createdon = $createdon;

        return $this;
    }






}
