<?php

namespace App\Enums;

enum FrequencyType: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case MonthlyOnDay = 'monthly_on_day';
    case EveryXWeeks = 'every_x_weeks';
}
