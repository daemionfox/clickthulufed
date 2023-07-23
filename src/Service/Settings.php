<?php

namespace App\Service;

use App\Helpers\SettingsHelper;
use Doctrine\ORM\EntityManagerInterface;

class Settings
{
    protected $settings;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $settingsHelper = SettingsHelper::init($entityManager);
        $this->settings = $settingsHelper->all();
    }

    public function get(): array
    {
        return $this->settings;
    }
}