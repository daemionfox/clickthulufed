<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\Page;
use App\Entity\Settings;
use App\Entity\User;
use App\Enumerations\NavigationTypeEnumeration;
use App\Exceptions\ClickthuluException;
use App\Exceptions\PageException;
use App\Exceptions\SettingNotFoundException;
use App\Helpers\NavigationHelper;
use App\Helpers\SettingsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class ContentController extends AbstractController
{


    #[Route('/@{ident}', name: 'app_content')]
    public function index(EntityManagerInterface $entityManager, string $ident, ?string $pageslug): Response
    {
        // TODO - Remove once we build the feed
        // Determines whether or not it's a comic or a user and either presents the appropriate comic page, or the user profile

        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $ident]);
        $this->setupCustomTemplate($comic);

        /**
         * @var User $user
         */
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $ident]);

        if (!empty($comic)) {
            return $this->comicPage($entityManager, $comic);
        }

        if (!empty($user)) {
            return $this->userIndex($user);
        }


        return $this->render('content/not_found.html.twig', []);
    }

    #[Route('/@{ident}/page/{pageslug}', name: 'app_comicpage')]
    public function page(EntityManagerInterface $entityManager, string $ident, string $pageslug): Response
    {
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $ident]);

        return $this->comicPage($entityManager, $comic, $pageslug);
    }


    protected function comicPage(EntityManagerInterface $entityManager, Comic $comic, ?string $pageslug = null)
    {
        if(empty($pageslug)) {
            $query = $entityManager->getRepository(Page::class)
                ->createQueryBuilder('p')
                ->where('p.publishdate < CURRENT_DATE()')
                ->andWhere('p.comic = :comic')
                ->setParameter(':comic', $comic)
                ->orderBy('p.publishdate', 'desc')
                ->setMaxResults(1);
            $result = $query->getQuery()->execute();
            if (empty($result)) {
                throw new PageException("No such page");
            }
            $page = $result[0];
            $comic->setCurrentpage($page);
            $navigation = NavigationHelper::init($entityManager, $page);
        } else {
            /**
             * @var Page $page
             */

            switch ($comic->getNavigationtype()) {
                case NavigationTypeEnumeration::NAV_ID:
                    $type = 'id';
                    /**
                     * @var Page $page
                     */
                    $page = $entityManager->getRepository(Page::class)->findOneBy([$type => $pageslug]);
                    break;
                case NavigationTypeEnumeration::NAV_TITLE:
                    $type = 'titleslug';
                    /**
                     * @var Page $page
                     */
                    $page = $entityManager->getRepository(Page::class)->findOneBy([$type => $pageslug]);
                    break;
                case NavigationTypeEnumeration::NAV_DATE:
                default:
                    $pagestart = new \DateTime("{$pageslug} 00:00:00", new \DateTimeZone($comic->getSchedule()->getTimezone()));
                    $pageend = new \DateTime("{$pageslug} 23:59:59", new \DateTimeZone($comic->getSchedule()->getTimezone()));
                    $query = $entityManager->getRepository(Page::class)
                        ->createQueryBuilder('p')
                        ->where('p.publishdate < CURRENT_DATE()')
                        ->andWhere('p.publishdate >= :pstart')
                        ->andWhere('p.publishdate < :pend')
                        ->andWhere('p.comic = :comic')
                        ->setParameter(':comic', $comic)
                        ->setParameter(':pstart', $pagestart)
                        ->setParameter(':pend', $pageend)
                        ->orderBy('p.publishdate', 'desc')
                        ->setMaxResults(1);
                    $result = $query->getQuery()->execute();
                    if (empty($result)) {
                        throw new PageException("No such page");
                    }
                    /**
                     * @var Page $page
                     */
                    $page = $result[0];
                    break;
            }
            $comic->setCurrentpage($page);
            $navigation = NavigationHelper::init($entityManager, $page);
        }




        return $this->render('@theme/page.html.twig', ['comic' => $comic, 'navigation' => $navigation]);
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




        $output = $this->render('public/comic.css.twig', ['comic' => $comic]);
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


        return $this->render('content/feed.html.twig', [
            'controller_name' => 'ContentController - User Feed',
        ]);
    }


    public function setupCustomTemplate(Comic $comic)
    {
        /**
         * @var Environment $env
         */
        $env = $this->container->get('twig');
        /**
         * @var FilesystemLoader $loader;
         */
        $loader = $env->getLoader();

        $default = __DIR__ . "/../../themes/default";
        $theme = $comic->getLayout()->getTheme();

        if (empty($theme)) {
            $theme = $default;
        }

        $loader->addPath($theme, 'theme');
        $env->setLoader($loader);
    }

}
