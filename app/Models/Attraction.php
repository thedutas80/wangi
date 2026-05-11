<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attraction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'duration',
        'active_status',
    ];

    protected function casts(): array
    {
        return [
            'active_status' => 'boolean',
            'duration' => 'integer',
        ];
    }

    public function activitySessions(): HasMany
    {
        return $this->hasMany(ActivitySession::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active_status', true);
    }
}
