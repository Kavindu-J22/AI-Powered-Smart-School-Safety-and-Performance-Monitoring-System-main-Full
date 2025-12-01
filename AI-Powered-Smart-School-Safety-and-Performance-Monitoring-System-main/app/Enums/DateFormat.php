<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;

enum DateFormat: string
{
    use InvokableCases;
    use Names;
    use Values;

    case DMY = 'd-m-Y';
    case YMD = 'Y-m-d';
}
