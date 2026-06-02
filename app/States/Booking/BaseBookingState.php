<?php

namespace App\States\Booking;

use App\Models\Booking;
use Exception;

abstract class BaseBookingState implements BookingState
{
    protected Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    protected function invalid(string $action): never
    {
        throw new \Exception("Cannot {$action} when booking is {$this->getStatus()}");
    }

    public function confirm(): BookingState
    {
        return $this->invalid('confirm');
    }

    public function complete(): BookingState
    {
        return $this->invalid('complete');
    }

    public function cancel(): BookingState
    {
        return $this->invalid('cancel');
    }
}
