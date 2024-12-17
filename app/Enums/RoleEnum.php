<?php

namespace App\Enums;

enum RoleEnum: int
{
    case admin = 2;
    case user = 3;
    case super = 4;
    case debugger  = 10;
}
