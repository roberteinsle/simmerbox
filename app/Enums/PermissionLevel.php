<?php

namespace App\Enums;

enum PermissionLevel: string
{
    case Everyone = 'everyone';
    case Household = 'household';
    case Owner = 'owner';
}
