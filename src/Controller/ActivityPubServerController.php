<?php

namespace App\Controller;


use ActivityPhp\Server;
use ActivityPhp\Server\Http\HttpSignature;
use ActivityPhp\Type;
use App\Entity\Comic;
use App\Entity\User;
use App\Exceptions\NotAllowedException;
use App\Helpers\SettingsHelper;
use App\Service\Settings;
use App\Traits\APServerTrait;
use App\Traits\MediaPathTrait;
use App\Traits\ResourceTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivityPubServerController extends AbstractController
{
    use ResourceTrait;
    use MediaPathTrait;
    use APServerTrait;

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
        $servername = preg_replace("/https?:\/\//", "", $server);
        list(,$name,) = preg_split('/([:@])/', $ident);
        $resource = $this->_getResource($entityManager, $name);
        $type = 'user';
        if (is_a($resource, Comic::class)) {
            $type = 'comic';
        }

        $data = [
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
        ];

        return new JsonResponse($data);
    }




    #[Route(
        '/@{ident}',
        name: 'app_apcontent',
        condition: "request.headers.get('Accept') matches '/application\\\\/activity\\\\+json/i'",
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

            $type = is_a($resource, Comic::class) ? 'Person' : 'Person';

            $aptype = is_a($resource, Comic::class) ? Type::create('Service') : Type::create('Actor');




            /**
             * @var Comic|User $resource
             */
            $data = [
                "@context" => [
                    "https://www.w3.org/ns/activitystreams",
                    "https://w3id.org/security/v1",
                    [
                        "schema" => "http://schema.org#",
                        "PropertyValue" => "schema:PropertyValue",
                        "value" => "schema:value",
                    ]
                ],
                "id"=> "{$server}/@{$ident}",
                "type"=> $type,
                "following"=> "{$server}/@{$ident}/following",  // Comics have no following, only followers
                "followers"=> "{$server}/@{$ident}/followers",  // Users have no followers, only following
                "preferredUsername" => $ident,
                "inbox"=> "{$server}/@{$ident}/inbox",
                "outbox"=> "{$server}/@{$ident}/outbox",
                "name" => $ident,
                "summary" => strip_tags($resource->getDescription()),
                "url" => "{$server}/@{$ident}",
                "manuallyApprovesFollowers" => false,
            	"discoverable" => true,
            	"published" => $resource->getCreatedon()->format('c'),
                "endpoints" => [
                    "sharedInbox" => "{$server}/@{$ident}/inbox",
                ],
                "publicKey" => [
                    "id" => "{$server}/@{$ident}#main-key",
                    "owner" => "{$server}/@{$ident}",
                    "publicKeyPem" => str_replace("\n", "", $resource->getPublickey()->getData())
                ]
            ];

            if (!empty($resource->getIconImageURL())) {
                $data["icon"] = [
                    "type" => "Image",
                    "mediaType" => $iconMimeType,
                    "url" => "{$server}{$resource->getIconImageURL()}",
                ];
            }

            if (is_a($resource, Comic::class) && !empty($resource->getLayout()->getHeaderimage())) {
                $headerImagePath = "{$mediaDir}/{$resource->getLayout()->getHeaderimage()}";
                $headerImageType = mime_content_type($headerImagePath);
                $data['image'] = [
                    'type' => 'Image',
                    'mediaType' => $headerImageType,
                    'url' => "{$server}/media/{$ident}/{$resource->getLayout()->getHeaderimage()}"
                ];
            }
            return new JsonResponse($data, 200, $headers);

        }

        throw new NotAllowedException("Cannot fetch details");
    }

    #[Route(
        '/@{ident}/outbox',
        name: 'app_apoutbox',
        condition: "request.headers.get('Accept') matches '/application\\\\/activity\\\\+json/i'",
    )]
    public function outbox(Settings $settings, EntityManagerInterface $entityManager, string $ident): Response
    {


    }


    #[Route(
        '/@{ident}/inbox',
        name: 'app_apinbox',
        condition: "request.headers.get('Accept') matches '/application\\\\/activity\\\\+json/i'",
    )]
    public function inbox(Request $request, EntityManagerInterface $entityManager, Settings $settings, LoggerInterface $logger, string $ident): Response
    {
        $logger->debug(__CLASS__ . "::" . __METHOD__ . " - Received POST to Inbox");
        $server = $this->_buildAPServer($settings);

        $validator = new HttpSignature($server);

        $isValid = $validator->verify($request);
        $body = $request->toArray();


        dd($isValid);


        // Follow
        // Undo Follow
        // Post Reply
        // Boost
        // Reply to Reply
        // Unboost
        // delete reply





        return new JsonResponse(['status' => 'success']);
    }


    protected function activityFollow($address)
    {

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


//    #[Route('/ap', name: 'app_apserver')]
//    public function server(Request $request, EntityManagerInterface $entityManager): Response
//    {
//        $server = new Server([
//            'logger' => [],
//            'instance' => [],
//            'cache' => [],
//            'http' => [],
//            'dialects' => [],
//            'ontologies' => []
//        ]);
//
//
//
//
//
//    }

}