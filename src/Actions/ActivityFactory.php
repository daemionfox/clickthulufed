<?php

namespace App\Actions;

use App\Exceptions\ActivityException;
use App\Service\Settings;
use Doctrine\ORM\EntityManagerInterface;

class ActivityFactory
{
    protected EntityManagerInterface $entityManager;
    protected Settings $settings;
    protected string $ident;

    public function __construct(EntityManagerInterface $entityManager,  Settings $settings, string $ident)
    {
        $this->entityManager = $entityManager;
        $this->settings = $settings;
        $this->ident = $ident;
    }

    public function create(array $data): ActivityAbstract
    {
        $type = $data['type'] ?? null;
        switch(strtoupper($type)) {
            case "FOLLOW":
                return new Follow($this->entityManager, $this->settings, $this->ident, $data);



            case "UNDO":
                $objType = $data['object']['type'] ?? null;
                if (empty($objType)) {
                    throw new ActivityException("Undo called with no referenced object");
                }
                switch(strtoupper($objType)) {
                    case "FOLLOW":
                        return new Unfollow($this->entityManager, $this->settings, $this->ident, $data);
                }



        }

    }

}