<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\Page;
use App\Entity\User;
use App\Enumerations\MediaPathEnumeration;
use App\Exceptions\ImageException;
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

    #[Route('/pageimage/{slug}/{file}', name: 'app_image')]
    public function getPage(string $slug, string $file, EntityManagerInterface $entityManager) : Response
    {

        /**
         * @var User $loggedinuser
         */
        $loggedinuser = $this->getUser();
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug]);
        $comicowner = $comic->getOwner();

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

        if ($this->comicUserMatch($loggedinuser, $comic)) {
            $canView = true;
        }

        if (!$canView) {
            return $this->getFileNotFound($entityManager);
        }


        try {
            return $this->getImage($comicowner->getUsername(), $slug, $file, MediaPathEnumeration::PATH_COMIC, $entityManager);
        } catch (ImageException) {
            return $this->getFileNotFound($entityManager);
        }
    }

    #[Route('/thumbnail/{slug}/{file}', name: 'app_thumbnail')]
    public function getThumbnail(string $slug, string $file, EntityManagerInterface $entityManager) : Response
    {

        /**
         * @var User $loggedinuser
         */
        $loggedinuser = $this->getUser();
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug]);
        $comicowner = $comic->getOwner();

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

        if ($this->comicUserMatch($loggedinuser, $comic)) {
            $canView = true;
        }

        if (!$canView) {
            return $this->getFileNotFound($entityManager);
        }


        try {
            return $this->getImage($comicowner->getUsername(), $slug, $file, MediaPathEnumeration::PATH_THUMBNAIL, $entityManager);
        } catch (ImageException) {
        }
        return $this->getPage($slug, $file, $entityManager);

    }

    #[Route('/castimage/{slug}/{file}', name: 'app_castimage')]
    public function getCast(string $slug, string $file, EntityManagerInterface $entityManager) : Response
    {
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug]);
        $owner = $comic->getOwner();
        try {
            return $this->getImage($owner->getUsername(), $slug, $file, MediaPathEnumeration::PATH_CAST, $entityManager);
        } catch (ImageException) {
            return $this->getFileNotFound($entityManager);
        }

    }


    #[Route('/media/{slug}/{file}', name: 'app_media')]
    public function get(string $slug, string $file, EntityManagerInterface $entityManager) : Response
    {
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug]);
        $owner = $comic->getOwner();
        try {
            return $this->getImage($owner->getUsername(), $slug, $file, MediaPathEnumeration::PATH_MEDIA, $entityManager);
        } catch (ImageException) {
            return $this->getFileNotFound($entityManager);
        }

    }

    #[Route('/usericon/@{username}/{file?}', name: 'app_usericon')]
    public function getUserIcon(EntityManagerInterface $entityManager, string $username, ?string $file) : Response
    {
        /**
         * @var User $user
         */
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        $settings = SettingsHelper::init($entityManager);

        $userPath = $this->getUserPath($settings, $user->getUsername());
        if(empty($file)) {
            $file = $user->getImage();
        }
        $filepath = "{$userPath}/_media/{$file}";

        if (!is_file($filepath)) {
            throw new ImageException("File not found");
        }
        return new BinaryFileResponse($filepath);
    }

    #[Route('/userbanner/@{user}/{file?}', name: 'app_userheader')]
    public function getUserHeader(EntityManagerInterface $entityManager, string $user, ?string $file) : Response
    {
        /**
         * @var User $user
         */
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $user]);

        $settings = SettingsHelper::init($entityManager);

        $userPath = $this->getUserPath($settings, $user->getUsername());

        if(empty($file)) {
            $file = $user->getHeaderImage();
        }
        $filepath = "{$userPath}/_media/{$file}";

        if (!is_file($filepath)) {
            throw new ImageException("File not found");
        }
        return new BinaryFileResponse($filepath);
    }

    protected function getImage(string $user, string $slug, string $file, string $type, EntityManagerInterface $entityManager)
    {
        $settings = SettingsHelper::init($entityManager);

        $path = $this->getMediaPath($settings, $user, $slug, $type);
        $filepath = "{$path}/{$file}";
        if (!is_file($filepath)) {
            throw new ImageException("File not found");
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