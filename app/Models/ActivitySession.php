<?php

namespace App\Models;

use App\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivitySession extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::retrieved(function (ActivitySession $session) {
            if ($session->status === SessionStatus::Active && $session->isPast()) {
                $session->status = SessionStatus::Inactive;
                $session->save();
            }
        });
    }

    protected $table = 'activity_sessions';

    protected $fillable = [
        'attraction_id',
        'date',
        'start_time',
        'end_time',
        'max_capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'start_time' => 'string',
            'end_time' => 'string',
            'max_capacity' => 'integer',
            'status' => SessionStatus::class,
        ];
    }

    public function attraction(): BelongsTo
    {
        return $this->belongsTo(Attraction::class);
    }

    public function guestAllocations(): HasMany
    {
        return $this->hasMany(GuestAllocation::class);
    }

    public function occupiedSeats(): int
    {
        return (int) $this->guestAllocations()->sum('pax');
    }

    public function availableSeats(): int
    {
        return max(0, $this->max_capacity - $this->occupiedSeats());
    }

    public function occupancyPercentage(): int
    {
        if ($this->max_capacity <= 0) {
            return 0;
        }

        return (int) round(($this->occupiedSeats() / $this->max_capacity) * 100);
    }

    public function isFull(): bool
    {
        return $this->availableSeats() <= 0;
    }

    public function isAlmostFull(): bool
    {
        return $this->occupancyPercentage() >= 80 && ! $this->isFull();
    }

    public function isPast(): bool
    {
        $sessionDate = $this->date->toDateString();
        $now = now();

        if ($sessionDate < $now->toDateString()) {
            return true;
        }

        if ($sessionDate === $now->toDateString() && $this->end_time <= $now->format('H:i')) {
            return true;
        }

        return false;
    }

    public function scopeActive($query)
    {
        return $query->where('status', SessionStatus::Active)
            ->where(function ($q) {
                $q->where('date', '>', now()->toDateString())
                    ->orWhere(function ($q) {
                        $q->where('date', '=', now()->toDateString())
                            ->where('end_time', '>', now()->format('H:i'));
                    });
            });
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }
}
