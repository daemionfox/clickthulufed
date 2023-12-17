<?php

namespace App\Traits;


use ActivityPhp\Server;
use App\Service\Settings;
use GuzzleHttp\Psr7\Uri;

trait APServerTrait
{
    protected function _buildAPServer(Settings $settings): Server
    {
        $uri = new Uri($settings->setting('server_url'));
        $port = $this->_getURIPort($uri);



        return new Server(
            [
                'instance' => [
                    'host' => $uri->getHost(),
                    'port' => $port,
                    'types' => 'ignore',
                    'debug' => 'true'
                ],
                'http' => [
                    'agent' => 'ClickthuluFedAgent'
                ]
            ]
        );
    }

    private function _getURIPort(Uri $uri): string
    {
        $port = $uri->getPort();
        if (!empty($port)) {
            return (string)$port;
        }

        $scheme = $uri->getScheme();
        return strtoupper($scheme) === 'HTTPS' ? 443 : 80;
    }

}