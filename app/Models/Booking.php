<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\States\Booking\BookingState;
use App\States\Booking\BookingStateFactory;

class Booking extends Model
{
    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function assign_rooms()
    {
        return $this->hasMany(BookingRoomList::class, 'booking_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'rooms_id', 'id');
    }

    // ✅ lịch sử trạng thái (optional nhưng nên có)
    public function statusHistories()
    {
        return $this->hasMany(BookingStatusHistory::class);
    }

    /*
    |--------------------------------------------------------------------------
    | STATE PATTERN
    |--------------------------------------------------------------------------
    */

    public function getState(): BookingState
    {
        return BookingStateFactory::make($this->status, $this);
    }

    public function transition(string $action): self
    {
        $state = $this->getState();

        try {
            $newState = match ($action) {
                'confirm' => $state->confirm(),
                'complete' => $state->complete(),
                'cancel' => $state->cancel(),
                default => throw new \Exception("Invalid action: {$action}"),
            };

            $oldStatus = $this->status;

            // ✅ set status
            $this->attributes['status'] = $newState->getStatus();
            $this->save();

            // ✅ log history
            // $this->logStatus($oldStatus, $this->status);

            // ✅ event (optional)
            // event(new \App\Events\BookingStatusChanged($this, $oldStatus, $this->status));

            return $this;

        } catch (\Exception $e) {
            throw new \Exception(
                "Cannot {$action} booking from status {$state->getStatus()}"
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PROTECT STATUS (không cho set bừa)
    |--------------------------------------------------------------------------
    */

    public function setStatusAttribute($value)
    {
        // chỉ cho phép set thông qua transition()
        if (!app()->runningInConsole()) {
            // dùng attributes để bypass recursion
            if (!array_key_exists('status', $this->attributes)) {
                $this->attributes['status'] = $value;
                return;
            }

            throw new \Exception('Do not set status manually. Use transition()');
        }

        $this->attributes['status'] = $value;
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS HISTORY
    |--------------------------------------------------------------------------
    */

    public function logStatus(string $from, string $to): void
    {
        $this->statusHistories()->create([
            'from' => $from,
            'to' => $to,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS (CLEAN CODE)
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'Confirmed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'Completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'Cancelled';
    }

    // ✅ helper pro
    public function isStatus(string $status): bool
    {
        return $this->status === $status;
    }
}