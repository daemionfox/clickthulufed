<?php

namespace App\Controller;


use ActivityPhp\Server\Http\WebFinger;
use ActivityPhp\Type;
use ActivityPhp\Type\AbstractObject;
use App\Entity\Comic;
use App\Entity\Subscriber;
use App\Entity\User;
use App\Exceptions\ComicNotFoundException;
use App\Exceptions\NotAllowedException;
use App\Exceptions\SettingNotFoundException;
use App\Helpers\SettingsHelper;
use App\Service\Settings;
use App\Traits\APServerTrait;
use App\Traits\MediaPathTrait;
use App\Traits\ResourceTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivityPubServerController extends AbstractController
{
    use ResourceTrait;
    use MediaPathTrait;
    use APServerTrait;

    const MAX_IN_LIST = 50;

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/.well-known/webfinger', name: 'app_webfinger')]
    public function webfinger(Request $request, Settings $settings, EntityManagerInterface $entityManager): Response
    {
        $ident = $request->query->get('resource');
        $server = $settings->setting('server_url');
        $headers = [
            'Content-Type' => 'application/ld+json'
        ];
        $servername = preg_replace("/https?:\/\//", "", $server);
        list(,$name,) = preg_split('/([:@])/', $ident);
        $resource = $this->_getResource($entityManager, $name);
        $type = 'user';
        if (is_a($resource, Comic::class)) {
            $type = 'comic';
        }

        $webfinger = new WebFinger(
            [
                'subject' => "acct:{$name}@{$servername}",
                'aliases' => [
                    "{$server}/{$type}/{$name}"
                ],
                'links' => [
                    [
                        'rel' => 'http://webfinger.net/rel/profile-page',
                        'type' => 'text-html',
                        'href' => "{$server}/@{$name}",
                    ],
                    [
                        'rel' => 'self',
                        'type' => 'application/activity+json',
                        'href' => "{$server}/@{$name}",

                    ]
                ]
            ]
        );

        return new JsonResponse($webfinger->toArray(), 200, $headers);
    }

    /**
     * @param Settings $settings
     * @param EntityManagerInterface $entityManager
     * @param string $ident
     * @return Response
     * @throws NotAllowedException
     * @throws SettingNotFoundException
     * @throws \Exception
     */
    #[Route(
        '/@{ident}',
        name: 'app_apcontent',
        condition: "request.headers.get('Accept') matches '/application\\\\/activity\\\\+json/i'",
    )]
    #[Route(
        '/@{ident}',
        name: 'app_apcontentld',
        condition: "request.headers.get('Accept') matches '/application\\\\/ld\\\\+json/i'",
    )]
    #[Route(
        '/comic/{ident}',
        name: 'app_apcontentcomic',
        condition: "request.headers.get('Accept') matches '/application\\\\/activity\\\\+json/i'",
    )]
    #[Route(
        '/comic/{ident}',
        name: 'app_apcontentcomicld',
        condition: "request.headers.get('Accept') matches '/application\\\\/ld\\\\+json/i'",
    )]
    #[Route(
        '/user/{ident}',
        name: 'app_apcontentuser',
        condition: "request.headers.get('Accept') matches '/application\\\\/activity\\\\+json/i'",
    )]
    #[Route(
        '/user/{ident}',
        name: 'app_apcontentuserld',
        condition: "request.headers.get('Accept') matches '/application\\\\/ld\\\\+json/i'",
    )]
    public function content(Settings $settings, EntityManagerInterface $entityManager, string $ident): Response
    {
        $resource = $this->_getResource($entityManager, $ident);
        $server = $settings->setting('server_url');
        $headers = [
            'Content-Type' => 'application/ld+json'
        ];

        if (is_a($resource, Comic::class) || is_a($resource, User::class)) {
            $settingsHelper = SettingsHelper::init($entityManager);
            $mediaDir = is_a($resource, Comic::class) ?
                $this->getMediaPath($settingsHelper, $resource->getOwner()->getUsername(), $resource->getSlug(), 'media') :
                $this->getUserPath($settingsHelper, $resource->getUsername()) . "/_media";


            $iconPath = !empty($resource->getImage()) ? "{$mediaDir}/{$resource->getImage()}" : null;
            $iconMimeType = mime_content_type($iconPath);

            /**
             * @var Type\Extended\AbstractActor $aptype
             */
            $aptype = is_a($resource, Comic::class) ? Type::create('Service') : Type::create('Person');

            $aptype
                ->set('@context', [
                    "https://www.w3.org/ns/activitystreams",
                    "https://w3id.org/security/v1",
                    [
                        "schema" => "http://schema.org#",
                        "PropertyValue" => "schema:PropertyValue",
                        "value" => "schema:value",
                    ]
                ])
                ->set('id', "{$server}/@{$ident}")
                ->set('following', "{$server}/@{$ident}/following")
                ->set('followers', "{$server}/@{$ident}/followers")
                ->set("preferredUsername", $ident)
                ->set("inbox", "{$server}/@{$ident}/inbox")
                ->set("outbox", "{$server}/@{$ident}/outbox")
                ->set("name", $ident)
                ->set("summary", strip_tags($resource->getDescription()))
                ->set("url", "{$server}/@{$ident}")
                ->set("published", $resource->getCreatedon()->format('c'))
                ->set('publicKey', [
                        "id" => "{$server}/@{$ident}#main-key",
                        "owner" => "{$server}/@{$ident}",
                        "publicKeyPem" => str_replace("\n", "", $resource->getPublickey()->getData())
                    ]
                )
            ;



            if (!empty($resource->getIconImageURL())) {
                $aptype->set("icon", [
                    "type" => "Image",
                    "mediaType" => $iconMimeType,
                    "url" => "{$server}{$resource->getIconImageURL()}",
                ]);
            }

            if (is_a($resource, Comic::class) && !empty($resource->getLayout()->getHeaderimage())) {
                $headerImagePath = "{$mediaDir}/{$resource->getLayout()->getHeaderimage()}";
                $headerImageType = mime_content_type($headerImagePath);
                $aptype->set("image", [
                    'type' => 'Image',
                    'mediaType' => $headerImageType,
                    'url' => "{$server}/media/{$ident}/{$resource->getLayout()->getHeaderimage()}"
                ]);
            }
            return new JsonResponse($aptype->toArray(), 200, $headers);

        }

        throw new NotAllowedException("Cannot fetch details");
    }

    #[Route(
        '/@{ident}/following',
        name: 'app_apfollowing',
        condition: "request.headers.get('Accept') matches '/application\\\\/activity\\\\+json/i'",
    )]
    #[Route(
        '/@{ident}/following',
        name: 'app_apfollowingld',
        condition: "request.headers.get('Accept') matches '/application\\\\/ld\\\\+json/i'",
    )]
    public function following(Settings $settings, EntityManagerInterface $entityManager, string $ident): Response
    {
        /**
         * @var Comic|User $resource
         */
        $resource = $this->_getResource($entityManager, $ident);
        if (is_a($resource, Comic::class)) {
            return new JsonResponse([]);  // Empty
        }

        // Users - Will eventually have a feed they follow
        // TODO - User Follow
        return new JsonResponse([]);  // Empty

    }


    /**
     * @throws SettingNotFoundException
     * @throws \Exception
     */
    #[Route(
        '/@{ident}/followers',
        name: 'app_apfollowers',
        condition: "request.headers.get('Accept') matches '/application\\\\/activity\\\\+json/i'",
    )]
    #[Route(
        '/@{ident}/followers',
        name: 'app_apfollowersld',
        condition: "request.headers.get('Accept') matches '/application\\\\/ld\\\\+json/i'",
    )]
    public function followers(Request $request, Settings $settings, EntityManagerInterface $entityManager, string $ident): Response
    {
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $ident]);
        if (empty($comic)) {
            throw new ComicNotFoundException("Could not find a comic with the name {$ident}");
        }
        $server = $settings->setting('server_url');
        $page = $request->get('page');

        if (!is_null($page)) {
            $aptype = $this->apFollowersPage($comic, $page, $server);
        } else {
            $aptype = $this->apFollowers($comic, $server);
        }



        $headers = [
            'Content-Type' => 'application/ld+json'
        ];

        return new JsonResponse($aptype->toArray(), 200, $headers);  // Empty

    }

    protected function apFollowers(Comic $comic, string $server): AbstractObject
    {
        $list = $comic->getSubscribers();
        $aptype = Type::create('OrderedCollection');
        $aptype
            ->set('@context', 'https://www.w3.org/ns/activitystreams')
            ->set('id', "{$server}/@{$comic->getSlug()}/followers")
            ->set('totalItems', count($list))
            ->set('first', "{$server}/@{$comic->getSlug()}/followers?page=1");
        return $aptype;
    }

    protected function apFollowersPage(Comic $comic, int $page, string $server): AbstractObject
    {
        $list = $comic->getSubscribers()->toArray();
        $size = count($list);
        $subset = array_slice($list, self::MAX_IN_LIST * ($page-1), self::MAX_IN_LIST);
        $orderedItems = [];
        /**
         * @var Subscriber $item
         */
        foreach ($subset as $item) {
            $orderedItems[] = $item->getSubscriber();
        }

        $last = $page * self::MAX_IN_LIST > $size;
        $next = !$last ? $page + 1 : null;
        $prev = $page !== 1 ? $page - 1 : null;

        $aptype = Type::create('OrderedCollectionPage');
        $aptype
            ->set('@context', 'https://www.w3.org/ns/activitystreams')
            ->set('id', "{$server}/@{$comic->getSlug()}/followers")
            ->set('totalItems', $size)
            ->set('first', "{$server}/@{$comic->getSlug()}/followers?page=1")
            ->set('partOf', "{$server}/@{$comic->getSlug()}/followers")
            ->set('orderedItems', $orderedItems)
        ;
        if (!empty($next)) {
            $aptype->set('next', "{$server}/@{$comic->getSlug()}/followers?page={$next}");
        }
        if (!empty($prev)) {
            $aptype->set('prev', "{$server}/@{$comic->getSlug()}/followers?page={$prev}");
        }
        return $aptype;
    }

    #[Route(
        '/@{ident}/outbox',
        name: 'app_apoutbox',
        condition: "request.headers.get('Accept') matches '/application\\\\/activity\\\\+json/i'",
    )]
    #[Route(
        '/@{ident}/outbox',
        name: 'app_apoutboxld',
        condition: "request.headers.get('Accept') matches '/application\\\\/ld\\\\+json/i'",
    )]
    public function outbox(Settings $settings, EntityManagerInterface $entityManager, string $ident): Response
    {

        return new JsonResponse(['status' => 'This is not working yet']);
    }

    /**
     * TODO - Manage Feed after Federation
     *
     * This is the placeholder for the future feed command.  /feed will return a list of all comics for a logged in
     * user's watch list.  That mechanism has yet to be determined, as it could include comics from other instances.
     *
     * @return Response
     */
    #[Route('/@{ident}/feed', name: 'app_ap_feed')]
    public function feed(EntityManagerInterface $entityManager, string $ident): Response
    {
        $resource = $this->_getResource($entityManager, $ident);

        if (is_a($resource, Comic::class)) {
            /**
             * @var Comic $resource
             */
            $pages = $resource->pagesTillToday();

            // Display page json going back to start as Feed


        } elseif (is_a($resource, User::class)) {

        }



        return new JsonResponse("Identity @{$ident} not found", 404);

    }

}