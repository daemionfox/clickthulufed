<?php

namespace App\Service;

use App\Exceptions\SettingNotFoundException;
use App\Helpers\SettingsHelper;
use Doctrine\ORM\EntityManagerInterface;

class Settings
{
    protected $settings;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $settingsHelper = SettingsHelper::init($entityManager);
        $this->settings = $settingsHelper->all();
        $sidebarCollapsed = isset($_COOKIE['clickthuluSidebarCollapsed']) ? (int)$_COOKIE['clickthuluSidebarCollapsed'] > 0 : false;
        $this->settings['user_sidebar_collapsed'] = $sidebarCollapsed;
    }

    public function get(): array
    {
        return $this->settings;
    }

    public function setting(string $key)
    {
        if (isset($this->settings[$key])) {
            return $this->settings[$key];
        }
        throw new SettingNotFoundException("Setting {$key} not found");
    }
}