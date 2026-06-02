<?php

namespace App\States\Booking;

use App\Models\Booking;

class BookingStateFactory
{
    public static function make(string $status, Booking $booking): BookingState
    {
        return match ($status) {
            'Pending' => new PendingState($booking),
            'Confirmed' => new ConfirmedState($booking),
            'Completed' => new CompletedState($booking),
            'Cancelled' => new CancelledState($booking),
            default => new PendingState($booking),
        };
    }
}
