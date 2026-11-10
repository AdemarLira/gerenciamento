<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\BookingRequest;
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
            'name' => 'Helena Duarte Odontologia',
            'slug' => 'helena-duarte',
            'email' => 'contato@helenaduarte.com.br',
            'phone' => '11987654321',
            'address' => 'Rua Oscar Freire, 000 — Jardins, São Paulo/SP',
            'primary_color' => '#B8955A',
            'welcome_message' => 'Cada sorriso é projetado, não padronizado.',
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
            ['Dra. Helena Duarte', 'Prótese e Estética', 'CRO-SP 000000', '#B8955A'],
            ['Dr. Rafael Moura', 'Implantodontia', 'CRO-SP 000001', '#4F7C93'],
            ['Dra. Lívia Prado', 'Ortodontia', 'CRO-SP 000002', '#7F9478'],
        ])->map(fn ($p) => Professional::create([
            'clinic_id' => $clinic->id, 'name' => $p[0], 'specialty' => $p[1], 'registry' => $p[2], 'color' => $p[3],
        ]));

        $patients = Patient::factory()->count(40)->create(['clinic_id' => $clinic->id]);

        // Pedidos de avaliação vindos do site (caixa "Solicitações").
        foreach ([
            ['Marina Costa', '11988887777', 'Lentes de contato dental', 'tarde', 'Gostaria de melhorar a cor e o formato dos dentes da frente.'],
            ['Ricardo Alves', '11977776666', 'Implantes', 'manha', null],
            ['Fernanda Lima', '11966665555', 'Clareamento', null, 'Tenho um casamento em dois meses.'],
        ] as $i => [$name, $phone, $procedure, $period, $message]) {
            BookingRequest::create([
                'clinic_id' => $clinic->id, 'name' => $name, 'phone' => $phone, 'procedure' => $procedure,
                'preferred_date' => Carbon::today()->addDays($i + 2), 'preferred_period' => $period,
                'message' => $message, 'status' => 'new',
            ]);
        }

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
                        'procedure' => collect(Appointment::PROCEDURES)->random(),
                        'status' => $d < 0 ? collect($statuses)->random() : collect(['scheduled', 'confirmed'])->random(),
                        'price' => collect([350, 600, 1200, 2500])->random(),
                    ]);
                }
            }
        }
    }
}
