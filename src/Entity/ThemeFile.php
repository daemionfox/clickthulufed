<?php

namespace App\Entity;

class ThemeFile
{
    protected ?string $filename;
    protected ?string $theme;
    protected ?string $data;

    /**
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string|null $filename
     */
    public function setFilename(?string $filename): ThemeFile
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTheme(): ?string
    {
        return $this->theme;
    }

    /**
     * @param string|null $theme
     */
    public function setTheme(?string $theme): ThemeFile
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @param string|null $data
     */
    public function setData(?string $data): ThemeFile
    {
        $this->data = $data;
        return $this;
    }




}