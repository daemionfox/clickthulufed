<?php

namespace App\Controller;


use App\Entity\Comic;
use App\Entity\User;
use App\Exceptions\NotAllowedException;
use App\Service\Settings;
use App\Traits\ResourceTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivityPubServerController extends AbstractController
{
    use ResourceTrait;

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
                    'href' => "{$server}/{$type}/{$name}",
                ],
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => "{$server}/{$type}/{$name}",

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
    #[Route(
        '/comic/{ident}',
        name: 'app_apcontentcomic',
        condition: "request.headers.get('Accept') matches '/application\\\\/activity\\\\+json/i'",
    )]
    public function content(Settings $settings, EntityManagerInterface $entityManager, string $ident): Response
    {
        $resource = $this->_getResource($entityManager, $ident);
        $server = $settings->setting('server_url');
        $headers = [
            'Content-Type' => 'application/ld+json'
        ];

        if (is_a($resource, Comic::class)) {
            /**
             * @var Comic $resource
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
                "id"=> "{{$server}}/comic/{{$ident}}",
                "type"=> "Comic",
                "following"=> "{{$server}}/comic/{{$ident}}/following",
                "followers"=> "{{$server}}/comic/{{$ident}}/followers",
                "inbox"=> "{{$server}}/comic/{{$ident}}/inbox",
                "outbox"=> "{{$server}}/comic/{{$ident}}/outbox",

            ];

            return new JsonResponse($data, 200, $headers);

        }

        throw new NotAllowedException("Cannot fetch details");
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