<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->startOfHour()->addDay();

        return [
            'starts_at' => $start,
            'ends_at' => $start->copy()->addMinutes(30),
            'type' => fake()->randomElement(Appointment::TYPES),
            'status' => 'scheduled',
            'price' => fake()->randomElement([150, 200, 250, 300]),
        ];
    }
}
