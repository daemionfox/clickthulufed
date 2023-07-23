<?php

namespace App\Enumerations;

use Eloquent\Enumeration\AbstractEnumeration;

class RoleEnumeration extends AbstractEnumeration
{
    const ROLE_OWNER = "Owner";
    const ROLE_ADMIN = "Admin";
    const ROLE_CREATOR = "Creator";
    const ROLE_USER = "User";

}