<?php

namespace App\Enums;

enum RegistrationMode: string
{
    case Open = 'open';
    case InviteOnly = 'invite_only';
}
