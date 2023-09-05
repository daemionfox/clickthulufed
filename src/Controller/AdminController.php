<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\InviteUser;
use App\Entity\RegistrationCode;
use App\Entity\Settings;
use App\Entity\SettingsCollection;
use App\Entity\User;
use App\Exceptions\ClickthuluException;
use App\Form\InviteUsersType;
use App\Form\SettingsType;
use App\Helpers\SettingsHelper;
use App\Security\EmailVerifier;
use App\Traits\BooleanTrait;
use App\Traits\ComicOwnerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    use BooleanTrait;
    use ComicOwnerTrait;

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
        } catch (Exception) {
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
        } catch (Exception) {
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
            return new RedirectResponse($this->generateUrl("/profile"), 403);
        }

        /**
         * @var User $delUser
         */
        $delUser = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if (in_array('ROLE_OWNER', $delUser->getRoles())) {
            $this->addFlash('error', 'Server Owner cannot be deleted');
            return new RedirectResponse($this->generateUrl('app_adminusers'), 403);
        }

        $delUser->setDeleted(true);
        $delUser->setRoles([]);
        $entityManager->persist($delUser);
        $entityManager->flush();;

        return new RedirectResponse($this->generateUrl('app_adminusers'));
    }


    #[Route('/admin/undeleteuser/{username}', name: 'app_undeleteuser')]
    public function undeleteUser(string $username, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!in_array("ROLE_OWNER", $user->getRoles()) && !in_array("ROLE_ADMIN", $user->getRoles())) {
            $this->addFlash('error', 'You do not have permission to perform this action');
            return new RedirectResponse($this->generateUrl("/profile"), 403);
        }

        /**
         * @var User $delUser
         */
        $delUser = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        $delUser->setDeleted(false);
        $delUser->setRoles([]);
        $entityManager->persist($delUser);
        $entityManager->flush();;

        return new RedirectResponse($this->generateUrl('app_adminusers'));
    }


    #[Route('/admin/settings', name: 'app_settings')]
    public function getSettings(EntityManagerInterface $entityManager, Request $request): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!in_array("ROLE_OWNER", $user->getRoles()) && !in_array("ROLE_ADMIN", $user->getRoles())) {
            $this->addFlash('error', 'You do not have permission to perform this action');
            return new RedirectResponse($this->generateUrl("/profile"), 403);
        }

        $items = $entityManager->getRepository(\App\Entity\Settings::class)->findAll();
        $settingsCollection = new SettingsCollection();
        /**
         * @var Settings $item
         */
        foreach ($items as $item) {
            $settingsCollection->addItem($item);
        }
        $form = $this->createForm(SettingsType::class, $settingsCollection);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                /**
                 * @var Settings $item
                 */
                foreach ($settingsCollection->getItems() as $item) {
                    $entityManager->persist($item);
                }
                $entityManager->flush();
                $this->addFlash('info', 'Settings have been updated');
                return new RedirectResponse($this->generateUrl('app_settings'));
            }
        } catch (\Exception $e){
            $err = new FormError($e->getMessage());
            $form->addError($err);
        }



        return $this->render(
            'admin/settings.html.twig',
            [
                'settingsForm' => $form->createView()
            ]
        );
    }

    #[Route('/admin/users/invite', name: 'app_admininviteusers')]
    public function inviteUsers(MailerInterface $mailer, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $settings = SettingsHelper::init($entityManager);

        /**
         * @var User $user
         */
        $user = $this->getUser();
        $invite = new InviteUser();

        $form = $this->createForm(InviteUsersType::class, $invite);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                $emails = $data->getUserArray();

                foreach ($emails as $email) {
                    $regcode = new RegistrationCode();
                    $regcode->setEmail($email)->generate();
                    $entityManager->persist($regcode);

                    $emailfrom = $settings->get('email_from_address');
                    if (empty($emailfrom)) {
                        throw new ClickthuluException("Email From address is not configured.");
                    }


                    $message = (new Email())
                        ->from($emailfrom)
                        ->to($email)
                        ->subject("You have been invited to {$settings->get('server_name')}")
                        ->html($this->render("registration/userinvite_email.html.twig", [ "user" => $user,  "reg" => $regcode ])->getContent());
                    $mailer->send($message);

                    $this->addFlash('info', "{$email} invited");

                }
                $entityManager->flush();
            }
        } catch (\Exception $e){
            $err = new FormError($e->getMessage());
            $form->addError($err);
        }

        return $this->render('admin/inviteusers.html.twig', [
            'inviteForm' => $form->createView()
        ]);
    }

    /**
     * @param string $string
     * @return string
     * @throws Exception
     */
    protected function getRole(string $string): string
    {
        switch(strtoupper($string)) {
            case "CREATOR":
                return "ROLE_CREATOR";
            case "ADMIN":
                return "ROLE_ADMIN";
        }
        throw new Exception("Role not found");
    }

}
