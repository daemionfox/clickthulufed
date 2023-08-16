<?php

namespace App\Controller;

use App\Entity\Cast;
use App\Entity\Comic;
use App\Entity\User;
use App\Enumerations\MediaPathEnumeration;
use App\Form\AddCastType;
use App\Helpers\SettingsHelper;
use App\Traits\ComicOwnerTrait;
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

class CastController extends AbstractController
{
    use ComicOwnerTrait;
    use MediaPathTrait;

    #[Route('/cast/{slug}', name: 'app_castmanage')]
    public function manageComicCast(string $slug, Request $request, EntityManagerInterface $entityManager): Response
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

        if (!$this->comicUserMatch($user, $comic)) {
            $this->addFlash("error", "You do not have permission to manage this comic");
        }

        return $this->render('cast/castlist.html.twig', [
            'comic' => $comic,
        ]);
    }



    #[Route('/cast/{slug}/add', name: 'app_addcast')]
    #[Route('/cast/{slug}/edit/{castid?}', name: 'app_editcast')]
    public function addEditCast(string $slug, ?int $castid, EntityManagerInterface $entityManager, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug] );
        /**
         * @var Cast $cast
         */
        $cast = new Cast();
        $cast->setComic($comic);

        if (!empty($castid)) {
            /**
             * @var Cast $cast
             */
            $cast = $entityManager->getRepository(Cast::class)->findOneBy(['id' => $castid]);
        }

        $form = $this->createForm(AddCastType::class, $cast);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($cast);
                $entityManager->flush();
                return new RedirectResponse($this->generateUrl('app_castmanage', ['slug' => $comic->getSlug()]));
            }
        } catch (\Exception $e){
            $err = new FormError($e->getMessage());
            $form->addError($err);
        }

        return $this->render(
            'cast/addedit_cast.html.twig',
            [
                'comic'=> $comic,
                'cast' => $cast,
                'addCastForm' => $form->createView()
            ]
        );

    }

    #[Route('/cast/{slug}/delete/{castid}', name: 'app_deletecast')]
    public function deleteCast(string $slug, int $castid, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug] );

        if (!$this->comicUserMatch($user, $comic)) {
            $this->addFlash('error', 'You do not have permission to delete this record.');
            return new RedirectResponse($this->generateUrl('app_castmanage', ['slug' => $slug]), 403);
        }


        $cast = $entityManager->getRepository(Cast::class)->find($castid);


        $entityManager->remove($cast);
        $entityManager->flush();
        return new RedirectResponse($this->generateUrl('app_castmanage', ['slug' => $slug]));
    }


    #[Route('/cast/{slug}/uploadimage', name: 'app_comicuploadcast')]
    public function uploadCastImage(string $slug, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var Comic $comic;
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug]);


        if (!$this->comicUserMatch($user, $comic)) {
            return new JsonResponse(['status' => 'failed', 'message' => "You do not have permission to upload images for this comic"], 400);
        }

        $settings = SettingsHelper::init($entityManager);

        $currentMax = ini_get('upload_max_filesize');
        $maxUpload = $settings->get('upload_max_filesize', $currentMax);
        ini_set('upload_max_filesize', $maxUpload);

        $castpath = $this->getMediaPath($settings, $user, $comic, MediaPathEnumeration::PATH_CAST);

        $files = array_pop($_FILES);
        if (empty($files)) {
            throw new FileNotFoundException("No file was uploaded.");
        }

        move_uploaded_file($files['tmp_name'], "{$castpath}/{$files['name']}");


        return new JsonResponse([
            'status' => 'uploaded',
            'comic' => $comic->getSlug(),
            'file' => $files['name']
        ]);


    }


}