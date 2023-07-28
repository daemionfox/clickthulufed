<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StringHashFilter extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('md5', [$this, 'md5Hash']),
            new TwigFilter('sha1', [$this, 'sha1Hash']),
            new TwigFilter('sha256', [$this, 'sha256Hash'])
        ];
    }

    public function md5Hash($string): string
    {
        return md5($string);
    }

    public function sha1Hash($string): string
    {
        return sha1($string);
    }

    public function sha256Hash($string): string
    {
        return hash('sha256', $string);
    }

}