<?php

namespace App\Service;

use App\Entity\Comic;
use Doctrine\ORM\EntityManagerInterface;

class AdminAlerts
{
    protected int $comicalert = 0;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $inactive = $entityManager->getRepository(Comic::class)->findBy(['isactive' => false, 'activatedon' => null]);
        $deleted = $entityManager->getRepository(Comic::class)->findBy(['isdeleted' => true]);
        $todelete = [];
        $inactiveCount = count($inactive);
        $thirtydays = \DateInterval::createFromDateSTring('1 month');
        $today = new \DateTime();
        /**
         * @var Comic $item
         */
        foreach ($deleted as $item) {
            $itemtime = $item->getDeletedon()->add($thirtydays);
            if ($itemtime <= $today) {
                $todelete[] = $item;
            }
        }
        $deletecount = count($todelete);
        $this->comicalert = (int)$inactiveCount + (int)$deletecount;
    }

    /**
     * @return int
     */
    public function getComicalert(): int
    {
        return $this->comicalert;
    }
}