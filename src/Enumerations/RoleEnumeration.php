<?php

namespace App\Enumerations;

use App\Exceptions\RoleNotFoundException;
use Eloquent\Enumeration\AbstractEnumeration;

class RoleEnumeration extends AbstractEnumeration
{
    const ROLE_OWNER = "ROLE_OWNER";
    const ROLE_ADMIN = "ROLE_ADMIN";
    const ROLE_CREATOR = "ROLE_CREATOR";
    const ROLE_USER = "ROLE_USER";

    public static function getRole(string $string): string
    {
        switch(strtoupper($string)){
            case "OWNER":
            case self::ROLE_OWNER:
                return self::ROLE_OWNER;
            case "ADMIN":
            case self::ROLE_ADMIN:
                return self::ROLE_ADMIN;
            case "CREATOR":
            case self::ROLE_CREATOR:
                return self::ROLE_CREATOR;
            case "USER":
            case self::ROLE_USER:
                return self::ROLE_USER;
        }
        throw new RoleNotFoundException("Role {$string} not found");
    }
}