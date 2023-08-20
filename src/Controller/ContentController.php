<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\Page;
use App\Entity\Settings;
use App\Entity\User;
use App\Exceptions\ClickthuluException;
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
    #[Route('/@{ident}/{?page}', name: 'app_comicpage')]
    public function index(EntityManagerInterface $entityManager, string $ident, ?string $page): Response
    {
        // TODO - Remove once we build the feed
        // Determines whether or not it's a comic or a user and either presents the appropriate comic page, or the user profile

        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $ident]);
        /**
         * @var User $user
         */
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $ident]);

        if (!empty($comic)) {
            return $this->comicPage($entityManager, $comic, $page);
        }

        if (!empty($user)) {
            return $this->userIndex($user);
        }


        return $this->render('content/not_found.html.twig', []);
    }


    protected function comicPage(EntityManagerInterface $entityManager, Comic $comic, ?string $pageslug = null)
    {
        if(empty($pageslug)) {
            $arr = (array) $comic->getPages()->getIterator();
            $page = array_pop($arr);
            $comic->setActivePage($page);
        }

        return $this->render('content/page.html.twig', ['comic' => $comic]);
    }



    #[Route('/@{slug}/css/style.css', name: 'app_customcomiccss')]
    public function customStyle(string $slug, EntityManagerInterface $entityManager):Response
    {
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug]);
        if (empty($comic)) {
            throw new ClickthuluException("No such comic");
        }




        $output = $this->render('content/components/comic.css.twig', ['comic' => $comic]);
        $output->headers->set('Content-type', 'text/css');

        return $output;


    }


    #[Route('/@{slug}/{page}', name: 'app_contentnaviation')]
    public function navigation(string $slug, string $page): Response
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
