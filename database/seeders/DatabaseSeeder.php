<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Cria uma clínica de demonstração com equipe, pacientes e agenda.
 * Login: admin@demo.com / senha: password
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $clinic = Clinic::create([
            'name' => 'Clínica Vida Plena',
            'slug' => 'vida-plena',
            'email' => 'contato@vidaplena.com.br',
            'phone' => '11987654321',
            'address' => 'Av. Paulista, 1000 — São Paulo/SP',
            'primary_color' => '#7C5CFF',
            'welcome_message' => 'Cuidado de verdade, do agendamento ao retorno.',
        ]);

        User::factory()->for($clinic)->admin()->create([
            'name' => 'Administrador Demo',
            'email' => 'admin@demo.com',
        ]);
        User::factory()->for($clinic)->create([
            'name' => 'Juliana Recepção',
            'email' => 'recepcao@demo.com',
        ]);

        $pros = collect([
            ['Dra. Ana Ribeiro', 'Dermatologia', 'CRM 123456', '#EC4899'],
            ['Dr. Carlos Mendes', 'Clínico Geral', 'CRM 654321', '#06B6D4'],
            ['Dra. Paula Santos', 'Psicologia', 'CRP 06/98765', '#10B981'],
        ])->map(fn ($p) => Professional::create([
            'clinic_id' => $clinic->id, 'name' => $p[0], 'specialty' => $p[1], 'registry' => $p[2], 'color' => $p[3],
        ]));

        $patients = Patient::factory()->count(40)->create(['clinic_id' => $clinic->id]);

        // Agenda de -14 a +14 dias
        $statuses = ['completed', 'completed', 'completed', 'no_show', 'canceled'];
        for ($d = -14; $d <= 14; $d++) {
            $day = Carbon::today()->addDays($d);
            if ($day->isWeekend()) {
                continue;
            }
            foreach ($pros as $pro) {
                $slots = collect(range(8, 17))->shuffle()->take(rand(3, 6));
                foreach ($slots as $hour) {
                    $start = $day->copy()->setTime($hour, rand(0, 1) * 30);
                    Appointment::create([
                        'clinic_id' => $clinic->id,
                        'patient_id' => $patients->random()->id,
                        'professional_id' => $pro->id,
                        'starts_at' => $start,
                        'ends_at' => $start->copy()->addMinutes(30),
                        'type' => collect(Appointment::TYPES)->random(),
                        'status' => $d < 0 ? collect($statuses)->random() : collect(['scheduled', 'confirmed'])->random(),
                        'price' => collect([150, 200, 250, 300])->random(),
                    ]);
                }
            }
        }
    }
}
