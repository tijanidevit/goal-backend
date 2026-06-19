<?php

namespace App\Enums;

enum EnumGoalCategory: string
{
    case SAVINGS = 'savings';
    case EMERGENCY = 'emergency';
    case INVESTMENT = 'investment';
    case DEBT = 'debt';
    case PURCHASE = 'purchase';
    case TRAVEL = 'travel';
    case OTHER = 'other';
}
