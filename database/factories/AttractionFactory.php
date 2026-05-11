<?php

namespace Database\Factories;

use App\Models\Attraction;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttractionFactory extends Factory
{
    protected $model = Attraction::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Cooking Class',
                'Batik Workshop',
                'Silver Craft',
                'Wood Carving',
                'Traditional Dance',
                'Off Road Adventure',
                'Snorkeling Trip',
                'Sunset Cruise',
                'Yoga Session',
                'Photography Tour',
            ]),
            'description' => fake()->sentence(15),
            'duration' => fake()->randomElement([30, 45, 60, 90, 120, 180]),
            'active_status' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active_status' => false,
        ]);
    }
}
