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
    public function admin_comics(EntityManagerInterface $entityManager): Response
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


        return $this->render('admin/comiclist.html.twig', [
            'comics' => $comics
        ]);
    }



    #[Route('/admin/users', name: 'app_adminusers')]
    public function admin_users(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!in_array('ROLE_OWNER', $user->getRoles()) || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->generateUrl("/profile"), 400);
        }

        $criteria = ['deleted' => false];
        if (in_array('ROLE_OWNER', $user->getRoles())) {
            $criteria = [];
        }

        $users = $entityManager->getRepository(User::class)->findBy($criteria);

        return $this->render('admin/userlist.html.twig', [
            'users' => $users
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $users = $entityManager->getRepository(User::class)->findBy(['username' => $slug]);
        $comics = $entityManager->getRepository(Comic::class)->findBy(['slug'=> $slug]);
        $passfail = (count($users) + count($comics));

        if ($passfail === 0) {
            return new Response("Unique Identifier is available");
        }
        return new Response("Unique Identifier is currently in use", 400);
    }


    #[Route('/comic/activate/{slug}', name: 'app_activatecomic')]
    public function activateComic(string $slug, Request $request, EntityManagerInterface $entityManager, Settings $settings): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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



    #[Route('/comic/delete/{slug}', name: 'app_deletecomic')]
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
            $this->addFlash('error', "You do not have permission to delete {$comic->getName()}");
            return new RedirectResponse($this->generateUrl("app_profile"), 400);
        }

        $comic->setIsdeleted(true);
        $comic->setDeletedon(new \DateTime());
        $entityManager->persist($comic);
        $entityManager->flush();
        return new RedirectResponse($this->generateUrl("app_profile"));

    }

    #[Route('/admin/addrole/{username}/{role}', name: 'app_addrole')]
    public function addRoleToUser(string $username, string $role, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!in_array("ROLE_OWNER", $user->getRoles()) && !in_array("ROLE_ADMIN", $user->getRoles())) {
            return new RedirectResponse($this->generateUrl("/profile"), 400);
        }
        try {

            /**
             * @var User $addto
             */
            $addto = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
            $oldroles = $addto->getRoles();
            $oldroles[] = $this->getRole($role);
            $addto->setRoles($oldroles);
            $addto->setRequestRole(null);
            $entityManager->persist($addto);
            $entityManager->flush();
        } catch (\Exception) {
            $this->addFlash('error', "Could not add role: {$role} to {$username}");
        }
        return new RedirectResponse($this->generateUrl("app_adminusers"));

    }


    #[Route('/admin/removerole/{username}/{role}', name: 'app_removerole')]
    public function removeRoleFromUser(string $username, string $role, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!in_array("ROLE_OWNER", $user->getRoles()) && !in_array("ROLE_ADMIN", $user->getRoles())) {
            return new RedirectResponse($this->generateUrl("/profile"), 400);
        }
        try {

            /**
             * @var User $remfrom
             */
            $remfrom = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
            $oldroles = $remfrom->getRoles();
            $newroles = [];
            $role = $this->getRole($role);
            foreach ($oldroles as $r) {
                if ($r !== $role) {
                    $newroles[] = $r;
                }
            }
            $remfrom->setRoles($newroles);
            $remfrom->setRequestRole(null);
            $entityManager->persist($remfrom);
            $entityManager->flush();
        } catch (\Exception) {
            $this->addFlash('error', "Could not remove role: {$role} from {$username}");
        }
        return new RedirectResponse($this->generateUrl("app_adminusers"));

    }



    #[Route('/admin/deleteuser/{username}', name: 'app_deleteuser')]
    public function deleteUser(string $username, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!in_array("ROLE_OWNER", $user->getRoles()) && !in_array("ROLE_ADMIN", $user->getRoles())) {
            $this->addFlash('error', 'You do not have permission to perform this action');
            return new RedirectResponse($this->generateUrl("/profile"), 400);
        }

        /**
         * @var User $delUser
         */
        $delUser = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if (in_array('ROLE_OWNER', $delUser->getRoles())) {
            $this->addFlash('error', 'Server Owner cannot be deleted');
            return new RedirectResponse($this->generateUrl('app_adminusers'), 400);
        }

        $delUser->setDeleted(true);
        $delUser->setRoles([]);
        $entityManager->persist($delUser);
        $entityManager->flush();;

        return new RedirectResponse($this->generateUrl('app_adminusers'));
    }



    /**
     * @param string $string
     * @return string
     * @throws \Exception
     */
    protected function getRole(string $string): string
    {
        switch(strtoupper($string)) {
            case "CREATOR":
                return "ROLE_CREATOR";
                break;
            case "ADMIN":
                return "ROLE_ADMIN";
        }
        throw new \Exception("Role not found");
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
