<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\Page;
use App\Entity\User;
use App\Enumerations\MediaPathEnumeration;
use App\Helpers\SettingsHelper;
use App\Traits\ComicOwnerTrait;
use App\Traits\MediaPathTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{

    use ComicOwnerTrait;
    use MediaPathTrait;

    #[Route('/image/{slug}/{file}', name: 'app_image')]
    public function getPage(string $slug, string $file, EntityManagerInterface $entityManager) : Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug]);
        $settings = SettingsHelper::init($entityManager);
        $canView = false;

        /**
         * @var Page $page
         */
        $page = $entityManager->getRepository(Page::class)->findOneBy(
            [
                'comic' => $comic,
                'image' => $file
            ]
        );

        $now = new \DateTime();

        if (!empty($page) && $page->getPublishdate() <= $now) {
            $canView = true;
        }

        if ($this->comicUserMatch($user, $comic)) {
            $canView = true;
        }

        if (!$canView) {
            return $this->getFileNotFound($entityManager);
        }

        $pagepath = $this->getMediaPath($settings, $user, $comic, MediaPathEnumeration::PATH_COMIC);
        $filepath = "{$pagepath}/{$file}";

        if (!is_file($filepath)) {
            return $this->getFileNotFound($entityManager);
        }
        return new BinaryFileResponse($pagepath);

    }



    #[Route('/thumbnail/{slug}/{file}', name: 'app_thumbnail')]
    public function getThumbnail(string $slug, string $file, EntityManagerInterface $entityManager) : Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug]);
        $settings = SettingsHelper::init($entityManager);
        $canView = false;

        /**
         * @var Page $page
         */
        $page = $entityManager->getRepository(Page::class)->findOneBy(
            [
                'comic' => $comic,
                'image' => $file
            ]
        );

        $now = new \DateTime();

        if (!empty($page) && $page->getPublishdate() <= $now) {
            $canView = true;
        }

        if ($this->comicUserMatch($user, $comic)) {
            $canView = true;
        }

        if (!$canView) {
            return $this->getFileNotFound($entityManager);
        }

        $thumbpath = $this->getMediaPath($settings, $user, $comic, MediaPathEnumeration::PATH_THUMBNAIL);
        $filepath = "{$thumbpath}/{$file}";
        if (!is_file($filepath)) {
            return $this->getPage($slug, $file, $entityManager);
        }
        return new BinaryFileResponse($filepath);

    }

    #[Route('/admin/ocrpage/{slug}/{file}', name: 'app_ocrpage')]
    public function ocrTranscript(string $slug, string $file, EntityManagerInterface $entityManager)
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
        $settings = SettingsHelper::init($entityManager);

        if (!$this->comicUserMatch($user, $comic)) {
            // This is where we have the server default failure image.
            return $this->getFileNotFound($entityManager);
        }







        return new JsonResponse();
    }

    protected function getFileNotFound(EntityManagerInterface $entityManager): BinaryFileResponse
    {
        $settings = SettingsHelper::init($entityManager);
        $image = $settings->get('noaccess_page_image', "assets/images/filenotfound.png");
        if (empty($image)) {
            $image = "assets/images/filenotfound.png"; // There needs to be something here.
        }
        if (!str_starts_with($image, '/')) {
            // Path is a relative path from the /app directory, prepend the app path to it
            $image = __DIR__ . "/../../{$image}";
            $image = realpath($image);
        }

        return new BinaryFileResponse($image);
    }
}