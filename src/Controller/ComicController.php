<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\Tag;
use App\Entity\User;
use App\Exceptions\SettingNotFoundException;
use App\Exceptions\TagException;
use App\Form\CreateComicType;
use App\Form\EditComicType;
use App\Helpers\SettingsHelper;
use App\Service\Settings;
use App\Traits\BooleanTrait;
use App\Traits\ComicOwnerTrait;
use App\Traits\MediaPathTrait;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComicController extends AbstractController
{
    use ComicOwnerTrait;
    use BooleanTrait;
    use MediaPathTrait;


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

    #[Route('/comic/{slug}/keygen', name: 'app_comickeygen')]
    public function regeneratePublicAndPrivateKeys(string $slug, Request $request, EntityManagerInterface $entityManager): Response
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
        $route = $request->headers->get('referer');

        if ($hasPerm) {
            $oldPublic = $comic->getPublickey();
            $oldPrivate = $comic->getPrivatekey();
            if (!empty($oldPublic)) {
                $entityManager->remove($oldPublic);
            }
            if (!empty($oldPrivate)) {
                $entityManager->remove($oldPrivate);
            }

            $comic->regenerateKeyPair();
            $entityManager->persist($comic);
            $entityManager->flush();
            return $this->redirect($route);
        }

    }

    #[Route('/comic/{slug}/activate', name: 'app_comicactivate')]
    public function activateComic(string $slug, Request $request, EntityManagerInterface $entityManager, Settings $settings): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $route = $request->headers->get('referer');

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
            return $this->redirect($route);
        }

        $this->addFlash('error', 'You do not have the permission to activate this comic.  Please see an administrator.');
        return $this->redirect($route, 400);
    }

    #[Route('/comic/{slug}/deactivate', name: 'app_comicdeactivate')]
    public function deactivateComic(string $slug, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $route = $request->headers->get('referer');
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
            return $this->redirect($route);
        }

        $this->addFlash('error', 'You do not have the permission to deactivate this comic.  Please see an administrator.');
        return $this->redirect($route, 400);

    }



    #[Route('/comic/{slug}/delete', name: 'app_comicdelete')]
    public function deleteComic(string $slug, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $route = $request->headers->get('referer');
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
            return $this->redirect($route, 403);
        }

        $entityManager->remove($comic);
        $entityManager->flush();

        return $this->redirect($route);

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
        $tags = $entityManager->getRepository(Tag::class)->findAll();
        if (empty($comic)) {
            try {
                $requireApproval = $settings->get('require_comic_approval');
            } catch (SettingNotFoundException) {
                $requireApproval = false;
            }
            $comic = new Comic($this->getParameter('piikey'));
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
                $taglist = $form->get('tags')->getData();
                $taglist = explode( ",", $taglist);
                $taglist = array_map('trim', $taglist);
                $comicTags = $comic->getTags();
                foreach($taglist as $tagstring) {
                    try {
                        $tag = $this->getTag($tagstring, $tags);
                        if (!$this->hasTag($comicTags, $tag)) {
                            $tag->addComic($comic);
                            $entityManager->persist($tag);
                        }
                    } catch (TagException){
                        $tag = new Tag();
                        $tag->setTag($tagstring);
                        $tag->addComic($comic);
                        $entityManager->persist($tag);
                    }
                }




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

    protected function hasTag(Collection $taglist, Tag $tag): bool
    {
        /**
         * @var Tag $t
         */
        foreach ($taglist as $t) {
            if ($t === $tag) {
                return true;
            }
        }
        return false;
    }

    protected function getTag(string $input, array $tags)
    {
        /**
         * @var Tag $tag
         */
        foreach ($tags as $tag) {
            if ($input === $tag->getTag()) {
                return $tag;
            }
        }
        throw new TagException("Tag {$input} not found");
    }

}