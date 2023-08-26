<?php

namespace App\Entity;

class Navigation
{

    private ?Page $current = null;
    private ?Page $first = null;
    private ?Page $prev = null;
    private ?Page $next = null;
    private ?Page $last = null;
    private ?Page $chapterStart = null;
    private ?Page $nextChapter = null;

    /**
     * @return? ?Page
     */
    public function getCurrent(): ?Page
    {
        return $this->current;
    }

    /**
     * @param Page|null $current
     */
    public function setCurrent(?Page $current): Navigation
    {
        $this->current = $current;
        return $this;
    }

    /**
     * @return ?Page
     */
    public function getFirst(): ?Page
    {
        return $this->first;
    }

    /**
     * @param Page|null $first
     */
    public function setFirst(?Page $first): Navigation
    {
        $this->first = $first;
        return $this;
    }

    /**
     * @return ?Page
     */
    public function getPrev(): ?Page
    {
        return $this->prev;
    }

    /**
     * @param Page|null $prev
     */
    public function setPrev(?Page $prev): Navigation
    {
        $this->prev = $prev;
        return $this;
    }

    /**
     * @return ?Page
     */
    public function getNext(): ?Page
    {
        return $this->next;
    }

    /**
     * @param Page|null $next
     */
    public function setNext(?Page $next): Navigation
    {
        $this->next = $next;
        return $this;
    }

    /**
     * @return ?Page
     */
    public function getLast(): ?Page
    {
        return $this->last;
    }

    /**
     * @param Page|null $last
     */
    public function setLast(?Page $last): Navigation
    {
        $this->last = $last;
        return $this;
    }

    /**
     * @return Page|null
     */
    public function getChapterStart(): ?Page
    {
        return $this->chapterStart;
    }

    /**
     * @param Page|null $chapterStart
     */
    public function setChapterStart(?Page $chapterStart): Navigation
    {
        $this->chapterStart = $chapterStart;
        return $this;
    }

    /**
     * @return Page|null
     */
    public function getNextChapter(): ?Page
    {
        return $this->nextChapter;
    }

    /**
     * @param Page|null $nextChapter
     */
    public function setNextChapter(?Page $nextChapter): Navigation
    {
        $this->nextChapter = $nextChapter;
        return $this;
    }
    
    

    
}