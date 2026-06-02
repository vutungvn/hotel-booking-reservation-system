<?php

namespace App\States\Booking;

use App\Models\Booking;
use Exception;

class PendingState extends BaseBookingState
{
    public function getStatus(): string
    {
        return 'Pending';
    }

    
    public function confirm(): BookingState
    {
        if ($this->booking->payment_status == 1) {
            return new ConfirmedState($this->booking);
        }

        return $this->invalid('confirm unpaid booking');
    }


    public function cancel(): BookingState
    {
        return new CancelledState($this->booking);
    }
}
