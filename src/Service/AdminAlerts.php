<?php

namespace App\Service;

use App\Entity\Comic;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AdminAlerts
{
    protected int $comic = 0;
    protected int $user = 0;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->comic = $this->comicAlerts($entityManager);
        $this->user = $this->userAlerts($entityManager);
    }


    protected function userAlerts(EntityManagerInterface $entityManager): int
    {
        $queryBuilder = $entityManager->createQueryBuilder();
        $roleresult = $queryBuilder->select('u')->from(User::class, 'u')->where($queryBuilder->expr()->isNotNull('u.requestRole'))->getQuery()->getResult();


        $userAlerts = (int)count($roleresult);

        return $userAlerts;
    }

    protected function comicAlerts(EntityManagerInterface $entityManager): int
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
        $comicalert = (int)$inactiveCount + (int)$deletecount;
        return $comicalert;
    }

    /**
     * @return int
     */
    public function getComicAlertCount(): int
    {
        return $this->comic;
    }

    /**
     * @return int
     */
    public function getUserAlertCount(): int
    {
        return $this->user;
    }
}