<?php

namespace App\Entity;

use App\Enumerations\NavigationTypeEnumeration;
use App\Exceptions\ClickthuluException;
use App\Repository\PageRepository;
use App\Traits\SlugTrait;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page
{
    use SlugTrait;

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
    private ?DateTimeInterface $publishdate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $transcript = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?DateTimeInterface $createdon = null;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $uploadedby = null;

    #[ORM\Column]
    private ?bool $deleted = false;

    #[ORM\ManyToMany(targetEntity: Cast::class, mappedBy: 'pages')]
    private Collection $casts;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titleslug = null;

    #[ORM\Column(options: ["default" => false])]
    private bool $pageAnnounced = false;

    public function __construct()
    {
        $this->createdon = new \DateTime();
        $this->casts = new ArrayCollection();
    }

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

    public function getPublishdate(): ?DateTimeInterface
    {
        return $this->publishdate;
    }

    public function setPublishdate(DateTimeInterface $publishdate): static
    {
        $this->publishdate = $publishdate;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    /**
     * @param ?string $info
     * @return Page
     */
    public function setInfo(?string $info): static
    {
        $this->info = $info;
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

    public function getCreatedon(): ?DateTimeInterface
    {
        return $this->createdon;
    }

    public function setCreatedon(DateTimeInterface $createdon): static
    {
        $this->createdon = $createdon;

        return $this;
    }

    public function getUploadedby(): ?User
    {
        return $this->uploadedby;
    }

    public function setUploadedby(?User $uploadedby): static
    {
        $this->uploadedby = $uploadedby;

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

    public function  isPublished(): bool
    {
        $now = new \DateTime();
        if ($this->deleted === true) {
            return false;
        }
        return $now >= $this->publishdate;
    }

    public function slug(): string
    {
        switch($this->comic->getNavigationtype()) {
            case NavigationTypeEnumeration::NAV_ID:
                return $this->getId();
            case NavigationTypeEnumeration::NAV_TITLE:
                return $this->titleSlug($this->title);
            case NavigationTypeEnumeration::NAV_DATE:
            default:
                return $this->publishdate->format('Y-m-d');
        }

    }



    public function calculateNextPublishDate(): \DateTime
    {
        $schedule = $this->comic->getSchedule();

        /**
         * @var ArrayCollection $pages
         */
        $pages = $this->comic->getPages();

        /**
         * @var \ArrayIterator $pageIterator
         */
        $pageIterator = $pages->getIterator();
        $pageIterator->uasort(function($a, $b){
            /**
             * @var Page $a
             * @var Page $b
             */
            if ($a->getPublishdate() === $b->getPublishdate()) {
                return 0;
            }
            return $a->getPublishdate() < $b->getPublishdate() ? -1 : 1;
        });
        $lastDate = new \DateTime();
        $lastPage = $pageIterator->current();
        if (!empty($lastPage) && $lastPage->getPublishdate() >= $lastDate) {
            $lastDate = $lastPage->getPublishdate();
        }

        $dayBool = [
            'Sunday' => $schedule->isSunday(),
            'Monday' => $schedule->isMonday(),
            'Tuesday' => $schedule->isTuesday(),
            'Wednesday' => $schedule->isWednesday(),
            'Thursday' => $schedule->isThursday(),
            'Friday' => $schedule->isFriday(),
            'Saturday' => $schedule->isSaturday()
        ];

        $next = [];

        foreach ($dayBool as $day => $bool) {
            if ($bool) {
                /**
                 * TODO - Untangle this kludge
                 */
                $next[] = strtotime("next {$day}", strtotime($lastDate->format('Y-m-d H:i')));
            }
        }
        sort($next);
        $nextDate = array_shift($next);

        $time = $schedule->getTime();

        $nextDT = new \DateTime();
        $nextDT->setTimestamp($nextDate);
        $nextDT->setTime($time->format('H'), $time->format('i'), 00);
        $nextDT->setTimezone(new \DateTimeZone($schedule->getTimezone()));
        return $nextDT;
    }

    /**
     * @return Collection<int, Cast>
     */
    public function getCasts(): Collection
    {
        return $this->casts;
    }

    public function addCast(Cast $cast): static
    {
        if (!$this->casts->contains($cast)) {
            $this->casts->add($cast);
            $cast->addPage($this);
        }

        return $this;
    }

    public function removeCast(Cast $cast): static
    {
        if ($this->casts->removeElement($cast)) {
            $cast->removePage($this);
        }

        return $this;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getTitleslug(): ?string
    {
        return $this->titleslug;
    }

    public function setTitleslug(?string $titleslug): static
    {
        $this->titleslug = $titleslug;

        return $this;
    }

    public function isPageAnnounced(): ?bool
    {
        return $this->pageAnnounced;
    }

    public function setPageAnnounced(bool $pageAnnounced): static
    {
        $this->pageAnnounced = $pageAnnounced;

        return $this;
    }

    public function getImageLink()
    {
        return "/pageimage/{$this->comic->getSlug()}/{$this->getImage()}";

    }
}
