<?php

namespace App\Enums;

enum RoleEnum: int
{
    case internal = 0;
    case external = 1;
    case admin = 2;
    case super = 4;
}
