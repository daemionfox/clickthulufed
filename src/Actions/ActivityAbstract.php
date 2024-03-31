<?php

namespace App\Actions;

use ActivityPhp\Server\Actor;
use ActivityPhp\Type\Extended\AbstractActor;
use ActivityPhp\Type\Extended\Activity\Accept;
use ActivityPhp\Type\Extended\Actor\Service;
use AP\ActivityPub\RequestSigner;
use AP\Exceptions\APSigningEmptyContentTypeException;
use AP\Exceptions\APSigningEmptyPostBodyException;
use AP\Exceptions\APSigningOpenSSLException;
use AP\Exceptions\APSigningUnsupportedVerbException;
use App\Entity\Comic;
use App\Exceptions\ComicNotFoundException;
use App\Helpers\SettingsHelper;
use App\Service\Settings;
use App\Traits\APServerTrait;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;

abstract class ActivityAbstract
{
    use APServerTrait;

    protected EntityManagerInterface $entityManager;
    protected string $ident;
    protected Settings $settings;
    protected array $headers;

    public function __construct(EntityManagerInterface $entityManager, Settings $settings, string $ident, array $data)
    {
        $this->entityManager = $entityManager;
        $this->ident = $ident;
        $this->settings = $settings;
    }

    /**
     * @return Comic
     * @throws ComicNotFoundException
     */
    public function getComic(): Comic
    {
        $comic = $this->entityManager->getRepository(Comic::class)->findOneBy(["slug" => $this->ident]);
        if (empty($comic)) {
            throw new ComicNotFoundException("Comic {$this->ident} not found");
        }
        return $comic;
    }

    public function getService(): AbstractActor
    {
        $server = $this->settings->setting('server_url');
        $comic = $this->getComic();
        $actor = new Service();
        $actor->set('@context', "https://www.w3.org/ns/activitystreams")
        ->set('id', "{$server}/@{$comic->getSlug()}")
        ->set('following', "{$server}/@{$comic->getSlug()}/following")
        ->set('followers', "{$server}/@{$comic->getSlug()}/followers")
        ->set("preferredUsername", $comic->getSlug())
        ->set("inbox", "{$server}/@{$comic->getSlug()}/inbox")
        ->set("outbox", "{$server}/@{$comic->getSlug()}/outbox")
        ->set("name", $comic->getSlug())
        ->set("summary", strip_tags($comic->getDescription()))
        ->set("url", "{$server}/@{$comic->getSlug()}")
        ->set("published", $comic->getCreatedon()->format('c'))
        ->set('publicKey', [
                "id" => "{$server}/@{$comic->getSlug()}#main-key",
                "owner" => "{$server}/@{$comic->getSlug()}",
                "publicKeyPem" => str_replace("\n", "", $comic->getPublickey()->getData())
            ]
        )
    ;
        return $actor;

    }

    /**
     * @param array $data
     * @throws ComicNotFoundException
     * @throws ErrorException
     * @throws GuzzleException
     * @throws APSigningEmptyContentTypeException
     * @throws APSigningEmptyPostBodyException
     * @throws APSigningOpenSSLException
     * @throws APSigningUnsupportedVerbException
     */
    public function AcceptActivity(array $data)
    {
        $signer = new RequestSigner();
        $server = $this->_buildAPServer($this->settings);
        $actor = $server->actor($data['actor']);
        $target = $actor->get('inbox');
        if (!is_string($target)) {
            throw new ErrorException("Bad inbox");
        }

        $comic = $this->getComic();
        /** @var string $keyID */
        $keyID = $actor->get('publicKey')['id'];
        /** @var string $inbox */
        $inbox = $actor->get('inbox');
        /** @var string $host */
        $host  = $actor->get('host');

        $accept = new Accept();
        $accept
            ->set('@context', "https://www.w3.org/ns/activitystreams")
            ->set('object', $data)
            ->set('actor', $this->getService()->toArray())
        ;
        $message = $accept->toJson();
        $hash = hash('sha256', $message, true);
        $digest = base64_encode($hash);
        $signHead = [
            "(request-target): post {$inbox}",
            "host: {$host}",
        ];
        $signedHeaders = $signer->signRequest($comic->getPrivatekey()->getData(), $keyID, time(), 'POST', $inbox, $body);
        


        $inbox = new Uri($target);
        $guzzle = new Client();
        $headers = [
            'Content-Type: application/activity+json',
            'User-Agent: ClickthuluFedAgent'
        ];
        $query = $guzzle->post(
            $inbox,
            [
                'headers' => $headers,
                'json' => $accept->toJson(),
            ]

        );
        $result = $query->getBody();

        $foo = 'bar';

    }

    abstract public function run(array $data): void;

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }
}