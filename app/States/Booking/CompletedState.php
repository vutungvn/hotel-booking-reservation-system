<?php

namespace App\States\Booking;

use App\Models\Booking;

class CompletedState extends BaseBookingState
{
    public function getStatus(): string
    {
        return 'Completed';
    }
}