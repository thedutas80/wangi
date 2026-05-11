<?php

namespace Database\Factories;

use App\Enums\AllocationSource;
use App\Models\ActivitySession;
use App\Models\GuestAllocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuestAllocationFactory extends Factory
{
    protected $model = GuestAllocation::class;

    public function definition(): array
    {
        return [
            'guest_name' => fake()->name(),
            'activity_session_id' => ActivitySession::factory(),
            'pax' => fake()->numberBetween(1, 6),
            'source' => fake()->randomElement(AllocationSource::cases()),
            'notes' => fake()->optional(0.3)->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
