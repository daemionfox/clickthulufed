<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\User;
use App\Enumerations\MediaPathEnumeration;
use App\Exceptions\SettingNotFoundException;
use App\Form\CreateComicType;
use App\Form\EditComicType;
use App\Form\UserProfileType;
use App\Helpers\SettingsHelper;
use App\Service\Settings;
use App\Traits\MediaPathTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    use MediaPathTrait;

    #[Route('/profile', name: 'app_profile')]
    public function profile(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('profile/profile.html.twig', []);

    }


    #[Route('/profile/edit', name: 'app_editprofile')]
    public function editProfile(EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);
        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('info', 'Profile has been updated.');
                return new RedirectResponse($this->generateUrl('app_profile'));
            }
        } catch (\Exception $e){
            $err = new FormError($e->getMessage());
            $form->addError($err);
        }

        return $this->render('profile/edit_profile.html.twig', [
            'profileForm' => $form->createView()
        ]);

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



    #[Route('/profile/upload', name: 'app_profileupload')]
    public function uploadImage(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $settings = SettingsHelper::init($entityManager);

        $currentMax = ini_get('upload_max_filesize');
        $maxUpload = $settings->get('upload_max_filesize', $currentMax);
        ini_set('upload_max_filesize', $maxUpload);

        $mediapath = $this->getUserPath($settings, $user->getUsername());
        $mediapath = "{$mediapath}/_media";
        if (!is_dir($mediapath)) {
            mkdir($mediapath, 0775, true);
        }
        $files = array_pop($_FILES);
        if (empty($files)) {
            throw new FileNotFoundException("No file was uploaded.");
        }

        move_uploaded_file($files['tmp_name'], "{$mediapath}/{$files['name']}");

        if (!is_file("{$mediapath}/{$files['name']}")) {
            throw new FileNotFoundException("File did not upload.");
        }


        return new JsonResponse([
            'status' => 'uploaded',
            'user' => $user->getUsername(),
            'file' => $files['name']
        ]);


    }


}
