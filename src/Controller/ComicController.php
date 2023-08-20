<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\User;
use App\Exceptions\SettingNotFoundException;
use App\Form\CreateComicType;
use App\Form\EditComicType;
use App\Helpers\SettingsHelper;
use App\Service\Settings;
use App\Traits\BooleanTrait;
use App\Traits\ComicOwnerTrait;
use App\Traits\MediaPathTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComicController extends AbstractController
{
    use ComicOwnerTrait;
    use BooleanTrait;
    use MediaPathTrait;
//
//    /**
//     * Generates a form for creating comics
//     *
//     * @param EntityManagerInterface $entityManager
//     * @param Request $request
//     * @param Settings $settings
//     * @return Response
//     */
//    #[Route('/comic/create', name: 'app_comiccreate')]
//    public function createComicForm(EntityManagerInterface $entityManager, Request $request, Settings $settings): Response
//    {
//        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
//
//        $comic = new Comic();
//        $form = $this->createForm(CreateComicType::class, $comic);
//        $form->handleRequest($request);
//        /**
//         * @var User $user;
//         */
//        $user = $this->getUser();
//        try {
//            $requireApproval = $settings->get()['require_comic_approval'];
//        } catch (SettingNotFoundException) {
//            $requireApproval = false;
//        }
//        try {
//            if ($form->isSubmitted() && $form->isValid()) {
//
//                $comic
//                    ->setIsactive(!$requireApproval)
//                    ->setOwner($user);
//
//                $entityManager->persist($comic);
//                $entityManager->flush();
//                return new RedirectResponse($this->generateUrl('app_profile'));
//            }
//        } catch (\Exception $e){
//            $err = new FormError($e->getMessage());
//            $form->addError($err);
//        }
//
//        return $this->render('comic/createcomic.html.twig', [
//            'comic' => $comic,
//            'createcomicForm' => $form->createView()
//        ]);
//    }


    /**
     * Checks the current comic and user database to see if an identifier is already in use.  If the id already exists, it throws an error
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/comic/checkslug/{slug}', name: 'app_comiccheckslug')]
    public function checkComicSlugForUniqueness($slug, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $users = $entityManager->getRepository(User::class)->findBy(['username' => $slug]);
        $comics = $entityManager->getRepository(Comic::class)->findBy(['slug'=> $slug]);
        $passfail = (count($users) + count($comics));

        if ($passfail === 0) {
            return new Response("Unique Identifier is available");
        }
        return new Response("Unique Identifier is currently in use", 400);
    }

    #[Route('/comic/{slug}/activate', name: 'app_comicactivate')]
    public function activateComic(string $slug, Request $request, EntityManagerInterface $entityManager, Settings $settings): Response
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
        $needsApproval = $settings->get()['require_comic_approval'];
        $canActivate = false;
        if (in_array("ROLE_OWNER", $user->getRoles()) || in_array("ROLE_ADMIN", $user->getRoles())) {
            $canActivate = true;
        } elseif ($hasPerm && !$this->toBool($needsApproval)) {
            $canActivate = true;
        }

        if ($canActivate) {
            $comic->setIsactive(true);
            $comic->setActivatedon(new DateTime());
            $entityManager->persist($comic);
            $entityManager->flush();
            return new RedirectResponse($this->generateUrl("app_profile"));
        }

        $this->addFlash('error', 'You do not have the permission to activate this comic.  Please see an administrator.');
        return new RedirectResponse($this->generateUrl("app_profile"), 400);
    }

    #[Route('/comic/{slug}/deactivate', name: 'app_comicdeactivate')]
    public function deactivateComic(string $slug, Request $request, EntityManagerInterface $entityManager): Response
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

        if ($hasPerm) {
            $comic->setIsactive(false);
            $entityManager->persist($comic);
            $entityManager->flush();
            return new RedirectResponse($this->generateUrl("app_profile"));
        }

        $this->addFlash('error', 'You do not have the permission to deactivate this comic.  Please see an administrator.');
        return new RedirectResponse($this->generateUrl("app_profile"), 400);

    }



    #[Route('/comic/{slug}/delete', name: 'app_comicdelete')]
    public function deleteComic(string $slug, Request $request, EntityManagerInterface $entityManager): Response
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
        $canDelete = $this->hasPermissions($user, $comic);

        if (!$canDelete) {
            $this->addFlash('error', "You do not have permission to delete this record");
            return new RedirectResponse($this->generateUrl("app_profile"), 403);
        }

        $entityManager->remove($comic);
        $entityManager->flush();

        return new RedirectResponse($this->generateUrl("app_profile"));

    }




    #[Route('/comic/{slug}/edit', name: 'app_editcomic')]
    #[Route('/create/comic', name: 'app_comiccreate')]
    public function editComic(?string $slug, Request $request, EntityManagerInterface $entityManager): Response
    {
        $settings = SettingsHelper::init($entityManager);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug]);
        if (empty($comic)) {
            try {
                $requireApproval = $settings->get('require_comic_approval');
            } catch (SettingNotFoundException) {
                $requireApproval = false;
            }
            $comic = new Comic();
            $comic->setOwner($user)->setIsactive($requireApproval);
        }

        $hasPerm = $this->hasPermissions($user, $comic);

        if (!$hasPerm) {
            $this->addFlash('error', "You do not have permissions to edit {$comic->getName()}");
            return new RedirectResponse($this->generateUrl("app_profile"), 400);
        }

        $form = $this->createForm(EditComicType::class, $comic);
        $form->handleRequest($request);

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

        return $this->render('comic/editcomic.html.twig', [
            'comic' => $comic,
            'editcomicForm' => $form->createView()
        ]);
    }



}