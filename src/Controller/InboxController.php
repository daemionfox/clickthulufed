<?php

namespace App\Controller;

use AP\ActivityPub\RequestSigner;
use AP\ActivityPub\SignatureValidator;
use AP\Exceptions\APSignatureException;
use AP\Type\Activity\Accept;
use AP\Type\Activity\Follow;
use AP\Type\Actor\Actor;
use AP\Type\Actor\Person;
use AP\Type\APObjectFactory;
use AP\Type\Core\AbstractAPObject;
use AP\Type\Core\APObject;
use App\Entity\Comic;
use App\Entity\Subscriber;
use App\Service\Settings;
use App\Traits\APServerTrait;
use App\Traits\ResourceTrait;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InboxController extends AbstractController
{
    use APServerTrait;
    use ResourceTrait;

    protected $headers;
    protected $profile;
    protected $payload;

    /**
     * @throws \AP\Exceptions\APObjectException
     * @throws \App\Exceptions\SettingNotFoundException
     */
    #[Route('/@{ident}/inbox', name: 'app_apinbox')]
    // condition: "request.headers.get('Accept') matches '/application\\\\/activity\\\\+json/i'",
    public function inbox(Request $request, EntityManagerInterface $entityManager, Settings $settings, LoggerInterface $logger, string $ident): Response
    {
        $logger->debug(__CLASS__ . "::" . __METHOD__ . " - Received POST to Inbox");
        $body = $request->toArray();
        $logger->notice("INCOMING BODY:");
        $logger->notice(json_encode($body, JSON_PRETTY_PRINT));
        $this->headers = $request->headers;
        $logger->notice("INCOMING HEADERS:");
        $logger->notice(json_encode( $request->headers, JSON_PRETTY_PRINT));
        $this->payload = $request->toArray();
        $actorRaw = $this->retrieveActor($body['actor']);
        /**
         * @var Actor $actor
         */
        $actor = APObjectFactory::create($actorRaw);

        // Actor PublicKey is losing it's keys for the key/value - Needs to be set properly.got

        $simpleRequest = $this->simplifyRequest($request);

        $validator = new SignatureValidator();
        $gmdate = date_create_from_format(DATE_RFC7231, $request->headers->get('date'));
        $time = intval($gmdate->format('U'));
        $publicKey = $actor->getPublicKey()->getPublicKeyPem();

        try {
            $validator->verifyRequestSignature($simpleRequest, $publicKey, $time);
        } catch (APSignatureException $apse){
            return new JsonResponse(['status' => 'error', 'message' => "Invalid Signature: {$apse->getMessage()}"], 400);
        }

        /**
         * @var Comic $comic
         */
        $comic = $this->_getResource($entityManager, $ident);

        /**
         * @var ?Accept $accept
         */
        $accept = null;
        /**
         * AbstractAPObject
         */
        $object = APObjectFactory::create($body);
        $server = $settings->setting('server_url');
        if (is_a($object, Follow::class)) {
            $sub = $entityManager->getRepository(Subscriber::class)->findOneBy(['subscriber' => $body['actor'], 'comic' => $comic]);
            if (!empty($sub)) {
                if ($sub->isIsdeleted()) {
                    $sub->setIsdeleted(false);
                    $entityManager->persist($sub);
                    $entityManager->flush();
                }
            } else {
                $sub = new Subscriber();
                $sub->setSubscriber($body['actor'])->setComic($comic);
                $entityManager->persist($sub);
                $entityManager->flush();
            }

            $accept = new Accept();
            $accept
                ->setID("{$server}/@{$comic->getSlug()}#accepts/follows/" . $object->getID())
                ->setActor("{$server}/comic/{$comic->getSlug()}")
                ->setObject($object)
                ;
        }

        if (is_a($accept, Accept::class)) {
            $now = time();
            $signer = new RequestSigner();
            $body = $accept->toJSON();

            $actorPath = parse_url($actor->getInbox(), PHP_URL_PATH);

            $headers = $signer->signRequest(
                $comic->getPrivatekey()->getData(),
                "{$server}/comic/{$comic->getSlug()}",
                $now,
                'post',
                $actor->getInbox(),
                $body,
                'application/activity+json'
            );

            $guzzle = new Client();
            $headers = array_merge([
                'User-Agent' => 'ClickthuluFedAgent'
            ], $headers);
            $headers = array_unique($headers);

// TODO - Remove
// For the moment, we're going to check the signature before we send it out, at least attempt to see what's going on.
//            $checkReq = [
//                'method' => 'post',
//                'path' => "/users/daemionfox/inbox", //$actor->getInbox(),
//                'headers' => $headers,
//                'body' => $accept->toJSON()
//            ];
//
//            $cvalidator = new SignatureValidator();
//            $cgmdate = date_create_from_format(DATE_RFC7231, $headers['Date']);
//            $ctime = intval($cgmdate->format('U'));
//            $cpublicKey = $comic->getPublicKey()->getData();
//
//            $foo = $cvalidator->verifyRequestSignature($checkReq, $cpublicKey, $ctime);
// End the temporary signature checking.


            try {
                $logger->notice("OUTGOING BODY:");
                $logger->notice(json_encode($body, JSON_PRETTY_PRINT));
                $logger->notice("OUTGOING HEADERS:");
                $logger->notice(json_encode($headers, JSON_PRETTY_PRINT));
                $query = $guzzle->post(
                    $actor->getInbox(),
                    [
                        'http_errors' => false,
                        'headers' => $headers,
                        'json' => $body,
                    ]
                );
            } catch (GuzzleException $ge) {
                $logger->notice("Error:");
                $logger->notice($ge->getMessage());
                $foo = 'bar';
            }
            $result = $query->getBody();
            $logger->notice($result->getContents());

// TODO - Remove - Curl call instead of Guzzle
//
//            $ch = curl_init($actor->getInbox());
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($ch, CURLOPT_POST, true);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
//            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//            $result = curl_exec($ch);


            // Okay, in theory this should be done.  Accept sent and everything's happy.
        }


        // x Follow
        // x Undo Follow
        // Post Reply
        // Boost
        // Reply to Reply
        // Unboost
        // delete reply





        return new JsonResponse(['status' => 'success']);
    }




    protected function retrieveActor($url): array
    {
        $client = new Client();
        $headers = [
            "Accept" => "application/activity+json"
        ];

        $response = $client->get($url, ["headers" => $headers]);
        $body = json_decode($response->getBody()->getContents(), true);
        return $body;
    }

    protected function simplifyRequest(Request $request): array
    {
        return [
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'headers' => [
                'content-type' => $request->headers->get('content-type'),
                'host' => $request->headers->get('host'),
                'date' => $request->headers->get('date'),
                'digest' => $request->headers->get('digest'),
                'signature' => $request->headers->get('signature')
            ],
            'body' => $request->getContent()
        ];
    }

    protected function splitRequestToValidator(Request $request, LoggerInterface $logger): void
    {
        try {
            $url = 'http://clickthulu-pasture_http_signature-1';
            $guzzle = new Client();
            $headers = $request->headers;
            $body = $request->getContent();
            $gHeaders = [];
            foreach ($headers as $key => $header) {
                    $gHeaders[$key] = $header;
            }

            $response = $guzzle->request('POST', $url, [
                'body' => $body,
                'headers' => $gHeaders
            ]
            );

            $rBody = $response->getBody()->getContents();
            $logger->info("HTTP SIG RESPONSE: $rBody");
        } catch (\Exception $e) {
            $fail = json_decode($e->getResponse()->getBody()->getContents());
            $failString = json_encode($fail, JSON_PRETTY_PRINT);
            $logger->error("HTTP SIG FAILED: {$failString}");
        }
    }

}