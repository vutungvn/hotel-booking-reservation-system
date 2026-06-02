<?php

namespace App\States\Booking;

use App\Models\Booking;
use Exception;
class ConfirmedState extends BaseBookingState
{
    public function getStatus(): string
    {
        return 'Confirmed';
    }

    public function complete(): BookingState
    {
        return new CompletedState($this->booking);
    }

    public function cancel(): BookingState
    {
        return new CancelledState($this->booking);
    }
}