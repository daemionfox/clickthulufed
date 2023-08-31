<?php

namespace App\Entity;

use App\Repository\CastRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CastRepository::class)]
class Cast
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Page::class, inversedBy: 'casts')]
    private Collection $pages;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTimeInterface $createdon = null;

    #[ORM\Column]
    private ?bool $deleted = null;

    #[ORM\ManyToOne(inversedBy: 'casts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Comic $Comic = null;

    public function __construct()
    {
        $this->createdon = new DateTime();
        $this->pages = new ArrayCollection();
        $this->deleted = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Page>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): static
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
        }

        return $this;
    }

    public function removePage(Page $page): static
    {
        $this->pages->removeElement($page);

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

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getComic(): ?Comic
    {
        return $this->Comic;
    }

    public function setComic(?Comic $Comic): static
    {
        $this->Comic = $Comic;

        return $this;
    }

    /**
     * @return Page
     * @throws \Exception
     */
    public function getFirstPage(): Page
    {
        $pages = (array)$this->pages->getIterator();
        /**
         * @var Page $a
         * @var Page $b
         */
        usort($pages, function($a, $b){
            return $a->getPublishDate() >= $b->getPublishDate() ? 1 : -1;
        });
        /**
         * @var Page $firstPage
         */
        $firstPage = array_shift($pages);
        return $firstPage;
    }

    /**
     * @return Page
     * @throws \Exception
     */
    public function getLastPage(): Page
    {
        $pages = (array)$this->pages->getIterator();
        /**
         * @var Page $a
         * @var Page $b
         */
        usort($pages, function($a, $b){
            return $a->getPublishDate() >= $b->getPublishDate() ? 1 : -1;
        });
        /**
         * @var Page $lastPage
         */
        $lastPage = array_pop($pages);
        return $lastPage;
    }


    public function getChoiceLabel(): string
    {
        return "<img class='img-icon' src='/castimage/{$this->Comic->getSlug()}/{$this->image}' alt='$this->name'> {$this->name}";
    }

}
