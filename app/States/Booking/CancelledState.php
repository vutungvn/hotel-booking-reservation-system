<?php

namespace App\States\Booking;

use App\Models\Booking;

class CancelledState extends BaseBookingState
{
    public function getStatus(): string
    {
        return 'Cancelled';
    }
}