<?php

namespace App\Enums;

enum EnumGoalStatus: string
{
    case ACTIVE = 'active';
    case ACHIEVED = 'achieved';
    case PAUSED = 'paused';
    case ABANDONED = 'abandoned';
}
