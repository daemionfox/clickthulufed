<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\Settings;
use App\Entity\User;
use App\Exceptions\SettingNotFoundException;
use App\Helpers\SettingsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContentController extends AbstractController
{
    #[Route('/@{ident}', name: 'app_content')]
    public function index(EntityManagerInterface $entityManager, $ident): Response
    {
        // TODO - Remove once we build the feed
        // Determines whether or not it's a comic or a user and either presents the appropriate comic page, or the user profile

        $comiccontent = $entityManager->getRepository(Comic::class)->findBy(['name' => $ident]);
        $usercontent = $entityManager->getRepository(User::class)->findBy(['username' => $ident]);



        return $this->render('content/index.html.twig', [
            'controller_name' => 'ContentController - Content',
        ]);
    }

    #[Route('/@{ident}/{slug}', name: 'app_naviation')]
    public function navigation($ident, $slug): Response
    {
        // Determines whether or not it's a comic or a user and either presents the appropriate comic page, or the user profile


        return $this->render('content/index.html.twig', [
            'controller_name' => 'ContentController - Navigation',
        ]);
    }

    #[Route('/feed', name: 'app_feed')]
    public function feed(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return new RedirectResponse($this->generateUrl("app_profile"));


        return $this->render('content/index.html.twig', [
            'controller_name' => 'ContentController - User Feed',
        ]);
    }

}
