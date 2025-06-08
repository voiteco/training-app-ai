<?php

namespace App\Entity;

enum BookingStatus: string
{
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
}