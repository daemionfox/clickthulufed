<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\User;
use App\Exceptions\SettingNotFoundException;
use App\Form\CreateComicType;
use App\Form\EditComicType;
use App\Service\Settings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{

    #[Route('/profile', name: 'app_profile')]
    public function profile(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('profile/profile.html.twig', []);

    }


    #[Route('/profile', name: 'app_editprofile')]
    public function editProfile(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('profile/edit_profile.html.twig', []);

    }


    #[Route('/profile/requestrole/creator', name: 'app_requestrolecreator')]
    public function requestRoleCreator(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */

        $user = $this->getUser();
        if (in_array("ROLE_OWNER", $user->getRoles()) || in_array("ROLE_ADMIN", $user->getRoles())) {
            return new Response('User already has administrative role', 200);
        } elseif (in_array("ROLE_CREATOR", $user->getRoles())) {
            return new Response('User has creator role', 200);
        }

        $user->setRequestRole('creator');
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('info', 'Creator role request added.');
        return new RedirectResponse($this->generateUrl("app_profile"));

    }


}
