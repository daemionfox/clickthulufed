<?php

namespace App\Actions;

use ActivityPhp\Type\Extended\Activity\Accept;
use App\Entity\Subscriber;
use App\Exceptions\ActivityException;
use App\Exceptions\ComicNotFoundException;

class Follow extends ActivityAbstract
{

    /**
     * @param array $data
     * @throws ComicNotFoundException
     * @throws ActivityException
     */
    public function run(array $data): void
    {
        $comic = $this->getComic();
        $actor = $data['actor'] ?? null;
        if (empty($actor)) {
            throw new ActivityException("Could not identify actor");
        }
        /**
         * @var Subscriber $sub
         */
        $sub = $this->entityManager->getRepository(Subscriber::class)->findOneBy(['subscriber' => $actor, 'comic' => $comic]);
        if (!empty($sub)) {
            if ($sub->isIsdeleted()) {
                $sub->setIsdeleted(false);
                $this->entityManager->persist($sub);
                $this->entityManager->flush();
            }
        } else {
            $sub = new Subscriber();
            $sub->setSubscriber($actor)->setComic($comic);
            $this->entityManager->persist($sub);
            $this->entityManager->flush();

        }


        $this->AcceptActivity($data);



    }


}