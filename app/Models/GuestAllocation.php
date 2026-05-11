<?php

namespace App\Models;

use App\Enums\AllocationSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_name',
        'activity_session_id',
        'pax',
        'source',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'pax' => 'integer',
            'source' => AllocationSource::class,
        ];
    }

    public function activitySession(): BelongsTo
    {
        return $this->belongsTo(ActivitySession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
