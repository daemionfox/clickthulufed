<?php

namespace App\Traits;

trait SlugTrait
{


    protected function titleSlug(string $title): string
    {
        if (empty($title)) {
            return '';
        }
        $title = strtolower($title);
        $title = str_replace(' ', '-', $title);
        $title = preg_replace('/[^a-z0-9-_]/', '', $title);
        $title = preg_replace('/-+/', '-', $title);
        return $title;
    }

}