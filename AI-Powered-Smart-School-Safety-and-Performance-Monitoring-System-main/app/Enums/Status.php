<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Options;
use ArchTech\Enums\Values;

enum Status: int
{
    use InvokableCases;
    use Names;
    use Options;
    use Values;

    case ACTIVE = 1;
    case INACTIVE = 2;
    case DISABLE = 3;
}
