<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfessionalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'name' => fake()->name(),
            'specialty' => fake()->randomElement(['Clínico Geral', 'Dermatologia', 'Psicologia', 'Odontologia', 'Fisioterapia', 'Nutrição']),
            'registry' => 'CRM '.fake()->numerify('#####'),
            'color' => fake()->randomElement(['#7C5CFF', '#06B6D4', '#10B981', '#F59E0B', '#EC4899', '#3B82F6']),
            'is_active' => true,
        ];
    }
}
