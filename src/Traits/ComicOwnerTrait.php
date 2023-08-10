<?php

namespace App\Traits;

use App\Entity\Comic;
use App\Entity\User;

trait ComicOwnerTrait
{

    public function comicUserMatch(User $user, Comic $comic): bool
    {

//        $admins = $comic->getAdmin();
        $owner = $comic->getOwner();
        /**
         * @var User $owner
         */
        if($owner->getUserIdentifier() === $user->getUserIdentifier()) {
            return true;
        }

//
//        /**
//         * @var User $admin
//         */
//        foreach($admins as $admin) {
//            if ($admin->getUserIdentifier() === $user->getUserIdentifier()) {
//                return true;
//            }
//        }


        return false;
    }



    protected function hasPermissions(User $user, Comic $comic): bool
    {

        if (in_array('ROLE_OWNER', $user->getRoles())) {
            return true;
        }
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }
        return $this->comicUserMatch($user, $comic);
    }


}