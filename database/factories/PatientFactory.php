<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'name' => fake()->name(),
            'cpf' => fake()->unique()->numerify('###########'),
            'birth_date' => fake()->dateTimeBetween('-80 years', '-3 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['feminino', 'masculino']),
            'phone' => '119'.fake()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'insurance' => fake()->randomElement([null, 'Particular', 'Unimed', 'Bradesco Saúde', 'SulAmérica', 'Amil']),
        ];
    }
}
