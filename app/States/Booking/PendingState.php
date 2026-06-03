<?php

namespace App\States\Booking;

use App\Models\Booking;
use Exception;
use Carbon\Carbon;

class PendingState extends BaseBookingState
{
    public function getStatus(): string
    {
        return 'Pending';
    }

    
    public function confirm(): BookingState
    {
        
        // Rule 1: phải thanh toán
        if ($this->booking->payment_status != 1) {
            return $this->invalid('confirm unpaid booking');
        }

        // Rule 2: không confirm nếu quá ngày check-in
        $checkIn = Carbon::createFromFormat('d-m-Y', $this->booking->check_in)->startOfDay();

        if (now()->greaterThan($checkIn->endOfDay())) {
            return $this->invalid('confirm expired booking');
        }

        // All checks passed: transition to Confirmed state
        return new ConfirmedState($this->booking);
    }


    public function cancel(): BookingState
    {
        return new CancelledState($this->booking);
    }
}
