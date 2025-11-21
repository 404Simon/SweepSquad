<?php

declare(strict_types=1);

namespace App;

enum InviteType: string
{
    case Permanent = 'permanent';
    case SingleUse = 'single_use';
    case TimeLimited = 'time_limited';
}
