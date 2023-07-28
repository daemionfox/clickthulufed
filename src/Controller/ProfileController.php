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


    /**
     * Generates a form for creating comics
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return Response
     */
    #[Route('/create', name: 'app_createcomic')]
    public function createComicForm(EntityManagerInterface $entityManager, Request $request, Settings $settings): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $comic = new Comic();
        $form = $this->createForm(CreateComicType::class, $comic);
        $form->handleRequest($request);
        /**
         * @var User $user;
         */
        $user = $this->getUser();
        try {
            $requireApproval = $settings->get()['require_comic_approval'];
        } catch (SettingNotFoundException) {
            $requireApproval = false;
        }
        try {
            if ($form->isSubmitted() && $form->isValid()) {

                $comic
                    ->setIsactive(!$requireApproval)
                    ->setOwner($user);

                $entityManager->persist($comic);
                $entityManager->flush();
                return new RedirectResponse($this->generateUrl('app_profile'));
            }
        } catch (\Exception $e){
            $err = new FormError($e->getMessage());
            $form->addError($err);
        }

        return $this->render('profile/createcomic.html.twig', [
            'createcomicForm' => $form->createView()
        ]);
    }


    #[Route('/comic/edit/{slug}', name: 'app_editcomic')]
    public function editComic(string $slug, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug]);
        $hasPerm = $this->hasPermissions($user, $comic);

        if (!$hasPerm) {
            $this->addFlash('error', "You do not have permissions to edit {$comic->getName()}");
            return new RedirectResponse($this->generateUrl("app_profile"), 400);
        }

        $form = $this->createForm(EditComicType::class, $comic);
        $form->handleRequest($request);

        /**
         * @var User $user;
         */
        $user = $this->getUser();

        try {
            if ($form->isSubmitted() && $form->isValid()) {

                $entityManager->persist($comic);
                $entityManager->flush();
                return new RedirectResponse($this->generateUrl('app_profile'));
            }
        } catch (\Exception $e){
            $err = new FormError($e->getMessage());
            $form->addError($err);
        }

        return $this->render('profile/editcomic.html.twig', [
            'comic' => $comic,
            'editcomicForm' => $form->createView()
        ]);
    }


}
