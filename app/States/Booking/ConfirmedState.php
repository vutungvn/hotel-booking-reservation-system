<?php

namespace App\States\Booking;

use App\Models\Booking;
use Exception;
use Carbon\Carbon;
class ConfirmedState extends BaseBookingState
{
    public function getStatus(): string
    {
        return 'Confirmed';
    }

    public function complete(): BookingState
    {   
        
        $checkIn = Carbon::createFromFormat('d-m-Y', $this->booking->check_in);

        // chưa đến ngày check-in thì không complete
        if (now()->lt($checkIn)) {
            return $this->invalid('complete before check-in');
        }

        return new CompletedState($this->booking);
    }

    public function cancel(): BookingState
    {
        return new CancelledState($this->booking);
    }
}