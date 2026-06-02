<?php

namespace App\States\Booking;

use App\Models\Booking;

interface BookingState
{
    public function __construct(Booking $booking);

    public function getStatus(): string;
    public function confirm(): BookingState;
    public function complete(): BookingState;
    public function cancel(): BookingState;
}
