<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClinicFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Clínica '.fake()->unique()->lastName(),
            'email' => fake()->companyEmail(),
            'phone' => '119'.fake()->numerify('########'),
            'primary_color' => '#7C5CFF',
            'opens_at' => '08:00',
            'closes_at' => '18:00',
            'slot_minutes' => 30,
        ];
    }
}
