<?php

namespace App\Traits;

use App\Entity\Comic;
use App\Entity\User;
use App\Helpers\SettingsHelper;

trait MediaPathTrait
{

    protected function getMediaPath(SettingsHelper $settingsHelper, string $user, string $comic, ?string $type): string
    {
        $start = $settingsHelper->get('user_storage_path', 'storage');
        if (!str_starts_with($start, '/')) {
            // Path is a relative path from the /app directory, prepend the app path to it
            $startpath = __DIR__ . "/../../{$start}";
            $startpath = realpath($startpath);
        }
        $base = "{$startpath}/{$user}/{$comic}";
        $final = "{$base}/{$type}";
        if (!is_dir($final)) {
            @mkdir($final, 0775, true);
        }

        return $final;
    }


    protected function getThemePath(SettingsHelper $settingsHelper, string $theme, ?Comic $comic): string
    {
        list($type, $theme) = explode("/", $theme);
        $systempath = $path = __DIR__ . "/../../themes";
        $canUseCustom = $settingsHelper->get('allow_custom_themes');

        if ($canUseCustom && strtoupper($type) === '@CUSTOM') {
            $path = $this->getMediaPath($this->systemSettings, $comic->getOwner()->getUsername(), $comic->getSlug(), 'themes');
        }

        if (is_dir("{$path}/{$theme}")) {
            return "{$path}/{$theme}";
        }
        return "{$systempath}/default";
    }

    protected function getThemes(SettingsHelper $settingsHelper, string $user, string $comic): array
    {
        $data = $this->getThemeData($settingsHelper, $user, $comic);
        $themes = [];
        foreach ($data['system'] as $theme) {
            $themes[$theme['display']] = $theme['path'];
        }
        foreach ($data['custom'] as $theme) {
            $themes[$theme['display']] = $theme['path'];
        }
        return $themes;
    }

    protected function getThemeData(SettingsHelper $settingsHelper, string $user, string $comic): array
    {
        $customPath = $this->getMediaPath($settingsHelper, $user, $comic, 'themes');

        $systemPath = __DIR__ . "/../../themes";
        $systemData = glob("{$systemPath}/*/data.json");
        $themes = [
            'system' => [],
            'custom' => []
        ];
        foreach ($systemData as $datafile) {
            $datum = json_decode(file_get_contents($datafile), true);
            $datum['display'] = "{$datum['theme']} (System)";
            $datum['path'] = "@system/{$datum['slug']}";
            $datum['type'] = 'system';
            $themes['system'][] = $datum;
        }

        $customData = glob("{$customPath}/*/data.json");
        foreach ($customData as $datafile) {
            $datum = json_decode(file_get_contents($datafile), true);
            $datum['display'] = "{$datum['theme']} (Custom)";
            $datum['path'] = "@custom/{$datum['slug']}";
            $datum['type'] = 'custom';
            $themes['custom'][] = $datum;
        }

        return $themes;
    }

}