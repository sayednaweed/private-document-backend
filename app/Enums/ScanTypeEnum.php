<?php

namespace App\Enums;

enum ScanTypeEnum: int
{
    case initail_scan = 1;
    case after_muqam_scan = 2;
    case final_scan = 3;
}
