<?php

namespace App\Models\User;

enum SubscriptionPlanNames: string
{
    case NONE = 'none';
    case PREMIUM = 'premium';
}