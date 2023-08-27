<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\User;
use App\Helpers\SettingsHelper;
use App\Traits\ComicOwnerTrait;
use App\Traits\MediaPathTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ThemeController extends AbstractController
{
    use MediaPathTrait;
    use ComicOwnerTrait;

    #[Route('/themes/{slug}', name: 'app_themelist')]
    public function themeList(EntityManagerInterface $entityManager, string $slug)
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
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug] );

        if (!$this->comicUserMatch($user, $comic)) {
            $this->addFlash("error", "You do not have permission to manage themes for this comic");
        }
        $themes = $this->getThemeData($settings, $user->getUsername(), $comic->getSlug());
        $themelist = [];



        return $this->render('themes/themelist.html.twig', ['comic' => $comic, 'themes' => $themes]);
    }

}