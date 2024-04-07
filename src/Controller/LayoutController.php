<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\Layout;
use App\Entity\User;
use App\Enumerations\MediaPathEnumeration;
use App\Form\LayoutType;
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

class LayoutController extends AbstractController
{
    use ComicOwnerTrait;
    use MediaPathTrait;

    #[Route('/layout/{slug}', name: 'app_layout')]
    public function updateLayout(string $slug, EntityManagerInterface $entityManager, Request $request): Response
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
            $this->addFlash("error", "You do not have permission to manage this comic");
        }

        $layout = $comic->getLayout();
        if (empty($layout)) {
            $layout = new Layout();
            $layout
                ->setComic($comic)
                ->setCss(
                "/** Custom CSS for **{$comic->getName()}** */\n"
                );
        }
        $settings = SettingsHelper::init($entityManager);
        $form = $this->createForm(LayoutType::class, $layout, ['settings' => $settings, 'comic' => $comic]);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($layout);
                $entityManager->flush();
                $this->addFlash('info', 'Layout updated');
                return new RedirectResponse($this->generateUrl('app_layout', ['slug' => $comic->getSlug()]));
            }
        } catch (\Exception $e){
            $err = new FormError($e->getMessage());
            $form->addError($err);
        }

        return $this->render('layout/layout.html.twig', [
            'comic' => $comic,
            'layoutForm' => $form->createView()
        ]);
    }



    #[Route('/layout/{slug}/upload', name: 'app_comicuploadheader')]
    public function uploadHeaderImage(string $slug, EntityManagerInterface $entityManager): Response
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

        $mediapath = $this->getMediaPath($settings, $comic->getOwner()->getUsername(), $comic->getSlug(), MediaPathEnumeration::PATH_MEDIA);

        $files = array_pop($_FILES);
        if (empty($files)) {
            throw new FileNotFoundException("No file was uploaded.");
        }

        move_uploaded_file($files['tmp_name'], "{$mediapath}/{$files['name']}");


        return new JsonResponse([
            'status' => 'uploaded',
            'comic' => $comic->getSlug(),
            'file' => $files['name']
        ]);


    }


}