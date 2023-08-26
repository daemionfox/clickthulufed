<?php

namespace App\Helpers;

use App\Entity\Navigation;
use App\Entity\Page;
use App\Exceptions\PageException;
use Doctrine\ORM\EntityManagerInterface;

class NavigationHelper
{
    protected EntityManagerInterface $entityManager;
    protected Navigation $navigation;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->navigation = new Navigation();

    }

    public static function init(EntityManagerInterface $entityManager, Page $page): Navigation
    {
        $me = new self($entityManager);
        $me->setPage($page);

        try {
            $first = $me->getFirstPage();
            $me->navigation->setFirst($first);
        } catch (PageException) {
            $me->navigation->setFirst($page);
        }
        try {
            $prev = $me->getPrevPage();
            $me->navigation->setPrev($prev);
        } catch (PageException) {
            $me->navigation->setPrev($page);
        }
        try {
            $next = $me->getNextPage();
            $me->navigation->setNext($next);
        } catch (PageException) {
            $me->navigation->setNext($page);
        }
        try {
            $last = $me->getLastPage();
            $me->navigation->setLast($last);
        } catch (PageException) {
            $me->navigation->setLast($page);
        }
        try {
            $start = $me->getChapterStart();
            $me->navigation->setChapterStart($start);
        } catch (PageException) {
            $me->navigation->setChapterStart($page);
        }
        try {
            $nextChapter = $me->getNextChapter();
            $me->navigation->setNextChapter($nextChapter);
        } catch (PageException) {
            $me->navigation->setNextChapter($page);
        }

        return $me->navigation;

    }

    public function setPage(Page $page): NavigationHelper
    {
        $this->navigation->setCurrent($page);
        return $this;
    }

    /**
     * @throws PageException
     */
    public function getChapterStart(?Page $start = null): Page
    {
        try {
            $nav = $this->navigation->getChapterStart();
            if (!empty($nav) && empty($start)) {
                return $nav;
            }
        } catch (\Error) { }

        $current = empty($start) ? $this->navigation->getCurrent() : $start;
        $chapter = $current->getChapter();

        $query = $this->entityManager->getRepository(Page::class)
            ->createQueryBuilder('p')
            ->where('p.comic = :comic')
            ->andWhere('p.chapter = :chapter')
            ->andWhere('p.publishdate < CURRENT_DATE()')
            ->setParameter(':comic', $current->getComic())
            ->setParameter(':chapter', $chapter)
            ->orderBy('p.publishdate', 'asc')
            ->setMaxResults(1);
        $result = $query->getQuery()->execute();
        if (empty($result)) {
            throw new PageException("No such page");
        }
        /**
         * @var Page $page
         */
        $page = $result[0];

        if ($page === $start){
            $prev = $this->getPrevPage();
            return $this->getChapterStart($prev);
        }

        return $page;
    }

    /**
     * @throws PageException
     */
    public function getNextChapter(?Page $start = null): Page
    {
        try {
            $nav = $this->navigation->getNextChapter();
            if (!empty($nav) && empty($start)) {
                return $nav;
            }
        } catch (\Error) { }

        $current = empty($start) ? $this->navigation->getCurrent() : $start;
        $chapter = $current->getChapter();

        $query = $this->entityManager->getRepository(Page::class)
            ->createQueryBuilder('p')
            ->where('p.comic = :comic')
            ->andWhere('p.chapter = :chapter')
            ->andWhere('p.publishdate < CURRENT_DATE()')
            ->setParameter(':comic', $current->getComic())
            ->setParameter(':chapter', $chapter)
            ->orderBy('p.publishdate', 'desc')
            ->setMaxResults(1);
        $result = $query->getQuery()->execute();
        if (empty($result)) {
            throw new PageException("No such page");
        }
        /**
         * @var Page $page
         */
        $page = $result[0];

        $next = $this->getNextPage($page);
        return $this->getChapterStart($next);
    }

    /**
     * @throws PageException
     */
    public function getPrevPage(?Page $start = null): Page
    {
        try {
            $nav = $this->navigation->getPrev();
            if (!empty($nav) && empty($start)) {
                return $nav;
            }
        } catch (\Error) { }


        $current = empty($start) ? $this->navigation->getCurrent() : $start;
        $query = $this->entityManager->getRepository(Page::class)
            ->createQueryBuilder('p')
            ->where('p.publishdate < :current')
            ->andWhere('p.comic = :comic')
            ->andWhere('p.publishdate < CURRENT_DATE()')
            ->setParameter(':current', $current->getPublishdate())
            ->setParameter(':comic', $current->getComic())
            ->orderBy('p.publishdate', 'desc')
            ->setMaxResults(1);
        $result = $query->getQuery()->execute();
        if (empty($result)) {
            throw new PageException("No such page");
        }
        /**
         * @var Page $page
         */
        $page = $result[0];
        return $page;
    }


    /**
     * @throws PageException
     */
    public function getNextPage(?Page $start = null): Page
    {
        try {
            $nav = $this->navigation->getNext();
            if (!empty($nav) && empty($start)) {
                return $nav;
            }
        } catch (\Error) { }

        $current = $this->navigation->getCurrent();
        $query = $this->entityManager->getRepository(Page::class)
            ->createQueryBuilder('p')
            ->where('p.publishdate > :current')
            ->andWhere('p.comic = :comic')
            ->andWhere('p.publishdate < CURRENT_DATE()')
            ->setParameter(':current', $current->getPublishdate())
            ->setParameter(':comic', $current->getComic())
            ->orderBy('p.publishdate', 'asc')
            ->setMaxResults(1);
        $result = $query->getQuery()->execute();
        if (empty($result)) {
            throw new PageException("No such page");
        }
        /**
         * @var Page $page
         */
        $page = $result[0];
        return $page;
    }


    /**
     * @throws PageException
     */
    public function getFirstPage(): Page
    {
        try {
            $nav = $this->navigation->getFirst();
            if (!empty($nav) && empty($start)) {
                return $nav;
            }
        } catch (\Error) { }

        $current = $this->navigation->getCurrent();
        $query = $this->entityManager->getRepository(Page::class)
            ->createQueryBuilder('p')
            ->where('p.publishdate < :current')
            ->andWhere('p.comic = :comic')
            ->andWhere('p.publishdate < CURRENT_DATE()')
            ->setParameter(':current', $current->getPublishdate())
            ->setParameter(':comic', $current->getComic())
            ->orderBy('p.publishdate', 'asc')
            ->setMaxResults(1);
        $result = $query->getQuery()->execute();
        if (empty($result)) {
            throw new PageException("No such page");
        }
        /**
         * @var Page $page
         */
        $page = $result[0];
        return $page;
    }

    /**
     * @throws PageException
     */
    public function getLastPage(): Page
    {
        try {
            $nav = $this->navigation->getLast();
            if (!empty($nav) && empty($start)) {
                return $nav;
            }
        } catch (\Error) { }

        $current = $this->navigation->getCurrent();
        $query = $this->entityManager->getRepository(Page::class)
            ->createQueryBuilder('p')
            ->where('p.publishdate < CURRENT_DATE()')
            ->andWhere('p.comic = :comic')
            ->setParameter(':comic', $current->getComic())
            ->orderBy('p.publishdate', 'desc')
            ->setMaxResults(1);
        $result = $query->getQuery()->execute();
        if (empty($result)) {
            throw new PageException("No such page");
        }
        /**
         * @var Page $page
         */
        $page = $result[0];
        return $page;
    }


}