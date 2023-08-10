<?php

namespace App\Traits;

use App\Entity\Comic;
use App\Entity\User;
use App\Helpers\SettingsHelper;

trait MediaPathTrait
{

    protected function getMediaPath(SettingsHelper $settingsHelper, User $user, Comic $comic, ?string $type): string
    {
        $start = $settingsHelper->get('comic_page_path', 'comicpages');
        if (!str_starts_with($start, '/')) {
            // Path is a relative path from the /app directory, prepend the app path to it
            $startpath = __DIR__ . "/../../{$start}";
            $startpath = realpath($startpath);
        }
        $base = "{$startpath}/{$user->getUsername()}/{$comic->getSlug()}";
        $final = "{$base}/{$type}";
        if (!is_dir($final)) {
            @mkdir($final, 0775, true);
        }

        return $final;
    }

}