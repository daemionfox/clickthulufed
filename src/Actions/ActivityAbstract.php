<?php

namespace App\Actions;

use ActivityPhp\Server\Actor;
use ActivityPhp\Type\Extended\AbstractActor;
use ActivityPhp\Type\Extended\Activity\Accept;
use ActivityPhp\Type\Extended\Actor\Service;
use App\Entity\Comic;
use App\Exceptions\ComicNotFoundException;
use App\Helpers\SettingsHelper;
use App\Service\Settings;
use App\Traits\APServerTrait;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;

abstract class ActivityAbstract
{
    use APServerTrait;

    protected EntityManagerInterface $entityManager;
    protected string $ident;
    protected Settings $settings;

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
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ErrorException
     */
    public function AcceptActivity(array $data)
    {
        $server = $this->_buildAPServer($this->settings);
        $actor = $server->actor($data['actor']);
        $target = $actor->get('inbox');
        if (!is_string($target)) {
            throw new \ErrorException("Bad inbox");
        }
        $accept = new Accept();
        $accept
            ->set('@context', "https://www.w3.org/ns/activitystreams")
            ->set('object', $data)
            ->set('actor', $this->getService()->toArray())

        ;
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

}