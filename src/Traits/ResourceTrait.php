<?php

namespace App\Traits;

use App\Entity\Comic;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

trait ResourceTrait
{

    protected function _getResource(EntityManagerInterface $entityManager, string $ident): Comic|User
    {
        $ident = trim($ident, '@');
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $ident]);

        /**
         * @var User $user
         */
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $ident]);


        if (!empty($comic)) {
            return $comic;
        }

        if (!empty($user)) {
            return $user;
        }

        throw new NotFoundResourceException("Identifier not found");

    }


}