<?php

namespace App\Enumerations;

use Eloquent\Enumeration\AbstractEnumeration;

class MediaPathEnumeration extends AbstractEnumeration
{

    const PATH_COMIC = 'comics';
    const PATH_THUMBNAIL = 'thumbnails';
    const PATH_CAST = 'cast';
    const PATH_MEDIA = 'media';

}