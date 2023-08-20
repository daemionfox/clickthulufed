<?php

namespace App\Enumerations;

use Eloquent\Enumeration\AbstractEnumeration;

class NavigationTypeEnumeration extends AbstractEnumeration
{
    const NAV_TITLE = 'title';
    const NAV_DATE = 'date';
    const NAV_ID = 'id';

    const LABEL_TITLE = 'Title based navigation. i.e.  /title-lowercase-and-dashed';
    const LABEL_DATE = 'Date based navigation.  i.e.  /2013-12-15';
    const LABEL_ID = 'ID based navigation. i.e. /361';

    static public function getChoices(): array
    {
        return [
            self::LABEL_TITLE => self::NAV_TITLE,
            self::LABEL_DATE => self::NAV_DATE,
            self::LABEL_ID => self::NAV_ID,
        ];
    }

}