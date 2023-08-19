<?php

namespace App\Controller;

use App\Entity\Chapter;
use App\Entity\Comic;
use App\Entity\User;
use App\Form\AddChapterType;
use App\Traits\ComicOwnerTrait;
use App\Traits\MediaPathTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChapterController extends AbstractController
{


    use ComicOwnerTrait;
    use MediaPathTrait;

    #[Route('/chapter/{slug}', name: 'app_chaptermanage')]
    public function manageChapters(string $slug, Request $request, EntityManagerInterface $entityManager): Response
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

        return $this->render('chapter/chapter_list.html.twig', [
            'comic' => $comic,
        ]);

    }



    #[Route('/chapter/{slug}/add', name: 'app_addchapter')]
    #[Route('/chapter/{slug}/edit/{chapterid?}', name: 'app_editchapter')]
    public function addEditComicChapter(string $slug, ?int $chapterid, EntityManagerInterface $entityManager, Request $request)
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

        /**
         * @var Chapter $chapter
         */
        $chapter = new Chapter();
        $chapter->setComic($comic);


        if (!empty($chapterid)) {
            /**
             * @var Chapter $chapter
             */
            $chapter = $entityManager->getRepository(Chapter::class)->findOneBy(['id' => $chapterid]);
        }


        $form = $this->createForm(AddChapterType::class, $chapter);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {



                $entityManager->persist($chapter);
                $entityManager->flush();
                return new RedirectResponse($this->generateUrl('app_chaptermanage', ['slug' => $comic->getSlug()]));
            }
        } catch (\Exception $e){
            $err = new FormError($e->getMessage());
            $form->addError($err);
        }

        return $this->render(
            'chapter/addedit_chapter.html.twig',
            [
                'comic' => $comic,
                'chapter' => $chapter,
                'addChapterForm' => $form->createView()
            ]
        );

    }


}