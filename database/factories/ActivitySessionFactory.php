<?php

namespace Database\Factories;

use App\Enums\SessionStatus;
use App\Models\ActivitySession;
use App\Models\Attraction;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivitySessionFactory extends Factory
{
    protected $model = ActivitySession::class;

    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 16);
        $duration = fake()->randomElement([1, 2, 3]);

        return [
            'attraction_id' => Attraction::factory(),
            'date' => fake()->dateTimeBetween('-1 week', '+2 weeks')->format('Y-m-d'),
            'start_time' => sprintf('%02d:00', $startHour),
            'end_time' => sprintf('%02d:00', $startHour + $duration),
            'max_capacity' => fake()->randomElement([10, 20, 25, 30, 40, 50]),
            'status' => SessionStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionStatus::Inactive,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionStatus::Blocked,
        ]);
    }

    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween('-2 weeks', '-1 day')->format('Y-m-d'),
        ]);
    }
}
