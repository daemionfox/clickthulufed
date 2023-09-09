<?php

namespace App\Helpers;

use App\Entity\Settings;
use App\Exceptions\SettingNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

class SettingsHelper
{
    protected static ?SettingsHelper $instance = null;

    protected array $settings = [];

    /**
     * @param EntityManagerInterface $entityManager
     * @return SettingsHelper|null
     */
    public static function init(EntityManagerInterface $entityManager)
    {
        if (empty(self::$instance)) {
            self::$instance = new self($entityManager);
        }
        return self::$instance;
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    private function __construct(EntityManagerInterface $entityManager)
    {
        $settings = $entityManager->getRepository(Settings::class)->findAll();
        /**
         * @var Settings $s
         */
        foreach ($settings as $s) {
            $this->settings[$s->getSetting()] = $s->getValue() !== null ? $s->getValue() : $s->getDefaultValue();
        }
    }

    /**
     * @param string $key
     * @return string
     * @throws SettingNotFoundException
     */
    public function get(string $key, ?string $default = null): string
    {

        if (isset($this->settings[$key])) {
            return $this->settings[$key];
        }

        if (!empty($default)){
            return $default;
        }

        throw new SettingNotFoundException("Could not find {$key}");
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->settings;
    }
}