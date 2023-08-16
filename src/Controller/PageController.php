<?php

namespace App\Controller;

use App\Entity\Cast;
use App\Entity\Comic;
use App\Entity\Page;
use App\Entity\User;
use App\Enumerations\MediaPathEnumeration;
use App\Form\AddPageType;
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

class PageController extends AbstractController
{
    use ComicOwnerTrait;
    use MediaPathTrait;


    #[Route('/page/{slug}', name: 'app_comicmanagepages')]
    public function manageComicPages(string $slug, Request $request, EntityManagerInterface $entityManager): Response
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

        return $this->render('page/pagelist.html.twig', [
            'comic' => $comic,
        ]);
    }


    #[Route('/page/{slug}/add', name: 'app_addpage')]
    #[Route('/page/{slug}/edit/{pageid?}', name: 'app_editpage')]
    public function addEditComicPage(string $slug, ?int $pageid, EntityManagerInterface $entityManager, Request $request)
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
         * @var Page $page
         */
        $page = new Page();
        $page->setComic($comic);
        $page->setPublishdate($page->calculateNextPublishDate());

        if (!empty($pageid)) {
            /**
             * @var Page $page
             */
            $page = $entityManager->getRepository(Page::class)->findOneBy(['id' => $pageid]);
        }


        $form = $this->createForm(AddPageType::class, $page);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $page->setUploadedby($user);

                /**
                 * @var Cast $cast
                 */
                foreach ($page->getCasts() as $cast) {
                    $cast->addPage($page);
                    $entityManager->persist($cast);
                }


                $entityManager->persist($page);
                $entityManager->flush();
                return new RedirectResponse($this->generateUrl('app_comicmanagepages', ['slug' => $comic->getSlug()]));
            }
        } catch (\Exception $e){
            $err = new FormError($e->getMessage());
            $form->addError($err);
        }

        return $this->render(
            'page/addedit_page.html.twig',
            [
                'comic'=>$comic,
                'page' => $page,
                'addPageForm' => $form->createView()
            ]
        );

    }


    #[Route('/page/{slug}/upload', name: 'app_pageuploadimage')]
    public function uploadPageImage(string $slug, EntityManagerInterface $entityManager): Response
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

        $comicpath = $this->getMediaPath($settings, $user, $comic, MediaPathEnumeration::PATH_COMIC);



        $files = array_pop($_FILES);
        if (empty($files)) {
            throw new FileNotFoundException("No file was uploaded.");
        }

        move_uploaded_file($files['tmp_name'], "{$comicpath}/{$files['name']}");

        $generateThumbnails = $settings->get('generate_thumbnails', true);

        if ($generateThumbnails) {
            $thumbpath = $this->getMediaPath($settings, $user, $comic, MediaPathEnumeration::PATH_THUMBNAIL);

            $source = imagecreatefromstring(file_get_contents("{$comicpath}/{$files['name']}"));
            $sourcesize = getimagesize("{$comicpath}/{$files['name']}");

            $sourceX = $sourcesize[0];
            $sourceY = $sourcesize[1];

            $newX = 200;
            $newY = $sourceY * $newX/$sourceX;

            $target = imagecreatetruecolor($newX, $newY);
            imagecopyresized($target, $source, 0,0, 0, 0, $newX, $newY, $sourceX, $sourceY);
            imagepng($target, "{$thumbpath}/{$files['name']}");  // TODO - Source file might not be png.

        }


        return new JsonResponse([
            'status' => 'uploaded',
            'comic' => $comic->getSlug(),
            'file' => $files['name']
        ]);


    }

}