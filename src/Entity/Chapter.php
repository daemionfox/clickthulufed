<?php

namespace App\Entity;

use App\Repository\ChapterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChapterRepository::class)]
class Chapter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'chapters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Comic $comic = null;

    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: Page::class)]
    private Collection $pages;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $page->setChapter($this);
        }

        return $this;
    }

    public function removePage(Page $page): static
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getChapter() === $this) {
                $page->setChapter(null);
            }
        }

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getfirstpage(): null|Page
    {
        $pages = $this->getsortedpages();
        $firstpage = array_shift($pages);
        return $firstpage;
    }

    public function getlastpage(): null|Page
    {
        $pages = $this->getsortedpages();
        $lastpage = array_pop($pages);
        return $lastpage;
    }

    public function getfirstpublishdate(): null|\DateTimeInterface
    {
        $firstpage = $this->getfirstpage();
        if ($firstpage !== null) {
            return $firstpage->getPublishdate();
        }
        return null;
    }

    public function getsortedpages(): array
    {
        try {
            /**
             * @var \ArrayIterator $pages
             */
            $pages = $this->getPages()->getIterator();

            /**
             * @var Page $a
             * @var Page $b
             */
            $pages->uasort(function($a, $b){
                return $a->getPublishDate() >= $b->getPublishDate() ? 1 : -1;
            });
            return $pages->getArrayCopy();
        } catch (\Exception $e) {
        }
        return [];
    }

    public function getisvisible(): bool
    {
        $firstpage = $this->getfirstpublishdate();
        if ($firstpage === null) {
            return false;
        }
        $now = new \DateTime();
        return $now >= $firstpage;
    }

    public function getChoiceLabel()
    {
        return $this->title;
    }


}
