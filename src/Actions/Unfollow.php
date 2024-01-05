<?php

namespace App\Actions;

use App\Entity\Subscriber;
use App\Exceptions\ActivityException;
use App\Exceptions\ComicNotFoundException;

class Unfollow extends ActivityAbstract
{

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
        if (empty($sub)) {
            return;
        }
        $sub->setIsdeleted(true);
        $this->entityManager->persist($sub);
        $this->entityManager->flush();


        $this->AcceptActivity($data);

        $foo = 'bar';
    }

}