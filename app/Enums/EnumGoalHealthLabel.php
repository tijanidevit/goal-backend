<?php

namespace App\Enums;

enum EnumGoalHealthLabel: string
{
    case ON_TRACK = 'On Track';
    case BEHIND = 'Behind';
    case SLIGHTLY_BEHIND = 'Slightly Behind';
    case AT_RISK = 'At Risk';
    case ACHIEVED = 'Achieved';
    case NO_PLAN = 'No Plan';
}
