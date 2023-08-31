<?php

namespace App\Controller;

use App\Entity\Cast;
use App\Entity\Comic;
use App\Entity\Page;
use App\Entity\User;
use App\Enumerations\NavigationTypeEnumeration;
use App\Exceptions\ClickthuluException;
use App\Exceptions\PageException;
use App\Helpers\NavigationHelper;
use App\Helpers\SettingsHelper;
use App\Traits\MediaPathTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ContentController extends AbstractController
{
    use MediaPathTrait;

    private SettingsHelper $systemSettings;

    /**
     * Okay, so, the Content controller is our main public facing code.  It handles all of the non-administrative
     * details for reading comic content.  Page collection, navigation, ancilliary pages, etc.  Unlike the administrative
     * functions, this is all running from the /@NAME/* route.
     *
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->systemSettings = SettingsHelper::init($entityManager);
    }

    /**
     * So, essential landing page.  Has two possible plays depending on the incoming slug.  If the slug matches a
     * comic slug, it presents the front page of the comic, alternatively, the slug could match a username, in which
     * case, it will present a user info page.
     *
     * @param EntityManagerInterface $entityManager
     * @param string $ident
     * @return Response
     * @throws PageException
     */
    #[Route('/@{ident}', name: 'app_content')]
    public function index(EntityManagerInterface $entityManager, string $ident): Response
    {
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
            return $this->userIndex($entityManager, $user);
        }


        return $this->render('@theme/not_found.html.twig', []);
    }

    /**
     * So, pulls details from a comic page based on the page's slug.  The slug can be in a number of formats, dictated
     * by the dropdown on the comic settings page.  Most common will be the date style slug (2023-08-24), however it could
     * also be either id (346) or by text string (this-page-is-first).  Slug style can be changed whenever without affecting
     * the navigation.
     *
     * @param EntityManagerInterface $entityManager
     * @param string $ident
     * @param string $pageslug
     * @return Response
     */
    #[Route('/@{ident}/page/{pageslug}', name: 'app_comicpage')]
    public function page(EntityManagerInterface $entityManager, string $ident, string $pageslug): Response
    {
        try {
            /**
             * @var Comic $comic
             */
            $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $ident]);
            $this->setupCustomTemplate($comic);
            return $this->comicPage($entityManager, $comic, $pageslug);
        } catch (PageException) {
        }
        return $this->render('@theme/not_found.html.twig', []);
    }


    /**
     * This is the actual piece that pulls the page out of the DB and assembles it for both the index and the page
     * methods.  It handles building out the Navigation Entity depending on which slug type is used.
     *
     * @param EntityManagerInterface $entityManager
     * @param Comic $comic
     * @param string|null $pageslug
     * @return Response
     * @throws PageException
     */
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

    /**
     * User index handler for the /@NAME route
     *
     * @param EntityManagerInterface $entityManager
     * @param User $user
     * @return Response
     */
    protected function userIndex(EntityManagerInterface $entityManager, User $user): Response
    {

        return $this->render('content/profile.html.twig', ['user' => $user]);
    }

    /**
     * Builds the custom CSS page from the Comic's Layout section
     *
     * @param string $slug
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws ClickthuluException
     */
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


    /**
     * TODO - Manage Feed after Federation
     *
     * This is the placeholder for the future feed command.  /feed will return a list of all comics for a logged in
     * user's watch list.  That mechanism has yet to be determined, as it could include comics from other instances.
     *
     * @return Response
     */
    #[Route('/feed', name: 'app_feed')]
    public function feed(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return new RedirectResponse($this->generateUrl("app_profile"));


        return $this->render('content/feed.html.twig', [
            'controller_name' => 'ContentController - User Feed',
        ]);
    }


    /**
     * Method to determine selected theme for a comic and add that path to Twig's loader under the @theme namespace
     *
     * @param Comic|null $comic
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig\Error\LoaderError
     */
    public function setupCustomTemplate(?Comic $comic)
    {
        /**
         * @var Environment $env
         */
        $env = $this->container->get('twig');
        /**
         * @var FilesystemLoader $loader;
         */
        $loader = $env->getLoader();

        $default = "@system/default";
        if (!empty($comic)) {
            $theme = $comic->getLayout()->getTheme();
        }

        if (empty($theme)) {
            $theme = $default;
        }

        $themePath = $this->getThemePath($this->systemSettings, $theme, $comic);

        $loader->addPath($themePath, 'theme');
        $env->setLoader($loader);
    }

    /**
     * Produces the archive page.
     *
     * @param EntityManagerInterface $entityManager
     * @param string $slug
     * @return Response
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig\Error\LoaderError
     */
    #[Route('/@{slug}/archive', name: 'app_archive')]
    public function archive(EntityManagerInterface $entityManager, string $slug): Response
    {
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug]);
        $this->setupCustomTemplate($comic);
        $comic->pagesTillToday();


        return $this->render('@theme/archive.html.twig', ['comic' => $comic, 'pages' => $comic->getPages()]);
    }

    #[Route('/@{slug}/archive/cast/{id}', name: 'app_castarchive')]
    public function castarchive(EntityManagerInterface $entityManager, string $slug, string $id): Response
    {
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug]);
        $this->setupCustomTemplate($comic);
        $comic->pagesTillToday();
        /**
         * @var Cast $cast
         */
        $cast = $entityManager->getRepository(Cast::class)->find($id);


        return $this->render('@theme/archive.html.twig', ['comic' => $comic, 'pages' => $cast->getPages()]);
    }

    #[Route('/@{slug}/cast', name: 'app_cast')]
    public function cast(EntityManagerInterface $entityManager, string $slug): Response
    {
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug]);
        $this->setupCustomTemplate($comic);
        $comic->pagesTillToday();


        return $this->render('@theme/cast.html.twig', ['comic' => $comic]);
    }
}
