<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\User;
use App\Exceptions\SettingNotFoundException;
use App\Form\CreateComicType;
use App\Form\EditComicType;
use App\Service\Settings;
use App\Traits\BooleanTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    use BooleanTrait;

    /**
     * @return Response
     */
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }


    #[Route('/admin/comics', name: 'app_admincomics')]
    public function admin_comics(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */

        $user = $this->getUser();
        if (!in_array('ROLE_OWNER', $user->getRoles()) || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->generateUrl("/profile"), 400);
        }

        $comics = $entityManager->getRepository(Comic::class)->findAll();


        return $this->render('admin/admin_comiclist.html.twig', [
            'comics' => $comics
        ]);
    }


    /**
     * Checks the current comic and user database to see if an identifier is already in use.  If the id already exists, it throws an error
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/create/checkslug/{slug}', name: 'app_admin')]
    public function checkComicSlugForUniqueness($slug, Request $request, EntityManagerInterface $entityManager): Response
    {

        $users = $entityManager->getRepository(User::class)->findBy(['username' => $slug]);
        $comics = $entityManager->getRepository(Comic::class)->findBy(['slug'=> $slug]);
        $passfail = (count($users) + count($comics));

        if ($passfail === 0) {
            return new Response("Unique Identifier is available");
        }
        return new Response("Unique Identifier is currently in use", 400);
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
                    ->addAdmin($user);

                $entityManager->persist($comic);
                $entityManager->flush();
                return new RedirectResponse($this->generateUrl('app_profile'));
            }
        } catch (\Exception $e){
            $err = new FormError($e->getMessage());
            $form->addError($err);
        }

        return $this->render('admin/createcomic.html.twig', [
            'createcomicForm' => $form->createView()
        ]);
    }

    #[Route('/comic/activate/{slug}', name: 'app_activatecomic')]
    public function activateComic(string $slug, Request $request, EntityManagerInterface $entityManager, Settings $settings): Response
    {
        $comicid = $request->query->get('comic');
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
            $comic->setActivatedon(new \DateTime());
            $entityManager->persist($comic);
            $entityManager->flush();
            return new RedirectResponse($this->generateUrl("app_profile"));
        }

        $this->addFlash('error', 'You do not have the permission to activate this comic.  Please see an administrator.');
        return new RedirectResponse($this->generateUrl("app_profile"), 400);

    }

    #[Route('/comic/deactivate/{slug}', name: 'app_deactivatecomic')]
    public function deactivateComic(string $slug, Request $request, EntityManagerInterface $entityManager): Response
    {
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

        return $this->render('admin/editcomic.html.twig', [
            'comic' => $comic,
            'editcomicForm' => $form->createView()
        ]);
    }


    #[Route('/comic/delete/{slug}', name: 'app_deletecomic')]
    public function deleteComic(string $slug, Request $request, EntityManagerInterface $entityManager): Response
    {
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
            $this->addFlash('error', "You do not have permission to delete {$comic->getName()}");
            return new RedirectResponse($this->generateUrl("app_profile"), 400);
        }

        $comic->setIsdeleted(true);
        $comic->setDeletedon(new \DateTime());
        $entityManager->persist($comic);
        $entityManager->flush();
        return new RedirectResponse($this->generateUrl("app_profile"));

    }


    protected function hasPermissions(User $user, Comic $comic)
    {

        if (in_array('ROLE_OWNER', $user->getRoles())) {
            return true;
        }
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        $admins = $comic->getAdmin();
        /**
         * @var User $admin
         */
        foreach($admins as $admin) {
            if ($admin->getUserIdentifier() === $user->getUserIdentifier()) {
                return true;
            }
        }
        return false;
    }

}
