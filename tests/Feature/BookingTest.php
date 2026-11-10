<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinic;
    private Professional $pro;
    private string $day;

    protected function setUp(): void
    {
        parent::setUp();
        // Segunda-feira, antes do expediente.
        Carbon::setTestNow(Carbon::parse('2026-11-09 07:00'));
        $this->day = '2026-11-10';

        $this->clinic = Clinic::factory()->create([
            'opens_at' => '08:00', 'closes_at' => '12:00', 'slot_minutes' => 30, 'phone' => '11988887777',
        ]);
        $this->pro = Professional::factory()->for($this->clinic)->create(['is_active' => true]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function book(array $over = [])
    {
        return $this->post('/agendar/'.$this->clinic->slug, array_merge([
            'professional_id' => $this->pro->id,
            'date' => $this->day,
            'time' => '09:00',
            'name' => 'Maria da Silva',
            'phone' => '(11) 91234-5678',
        ], $over));
    }

    public function test_landing_page_carrega_com_planos_e_whatsapp(): void
    {
        config(['app.sales_whatsapp' => '11999990000']);

        $this->get('/plataforma')->assertOk()
            ->assertSee('Planos')
            ->assertSee('Agendamento online')
            ->assertSee('https://wa.me/5511999990000', false);
    }

    public function test_pagina_publica_lista_horarios_livres(): void
    {
        Appointment::factory()->create([
            'clinic_id' => $this->clinic->id,
            'professional_id' => $this->pro->id,
            'patient_id' => Patient::factory()->for($this->clinic)->create()->id,
            'starts_at' => "{$this->day} 10:00", 'ends_at' => "{$this->day} 10:30", 'status' => 'confirmed',
        ]);

        $this->get("/agendar/{$this->clinic->slug}?profissional={$this->pro->id}&data={$this->day}")
            ->assertOk()
            ->assertSee('value="09:00"', false)
            ->assertDontSee('value="10:00"', false)
            ->assertSee('value="11:30"', false)
            ->assertDontSee('value="12:00"', false);
    }

    public function test_paciente_agenda_online_e_cadastro_e_criado(): void
    {
        $this->book()->assertRedirect('/agendar/'.$this->clinic->slug)->assertSessionHas('booked');

        $patient = Patient::withoutGlobalScopes()->where('phone', '11912345678')->firstOrFail();
        $this->assertSame($this->clinic->id, $patient->clinic_id);

        $appt = Appointment::withoutGlobalScopes()->firstOrFail();
        $this->assertSame($this->clinic->id, $appt->clinic_id);
        $this->assertSame('scheduled', $appt->status);
        $this->assertSame("{$this->day} 09:00", $appt->starts_at->format('Y-m-d H:i'));
        $this->assertSame(30, $appt->durationMinutes());

        // Segundo agendamento com o mesmo WhatsApp reutiliza o paciente.
        $this->book(['time' => '11:00']);
        $this->assertSame(1, Patient::withoutGlobalScopes()->count());
        $this->assertSame(2, Appointment::withoutGlobalScopes()->count());
    }

    public function test_nao_permite_agendar_horario_ocupado_ou_fora_do_expediente(): void
    {
        $this->book();
        $this->book(['phone' => '11900001111'])->assertSessionHasErrors('time');
        $this->book(['time' => '13:00'])->assertSessionHasErrors('time');
        $this->book(['time' => '09:15'])->assertSessionHasErrors('time');
        $this->book(['date' => '2026-11-15'])->assertSessionHasErrors('time'); // domingo

        $this->assertSame(1, Appointment::withoutGlobalScopes()->count());
    }

    public function test_nao_agenda_profissional_de_outra_clinica(): void
    {
        $other = Professional::factory()->for(Clinic::factory())->create();

        $this->book(['professional_id' => $other->id])->assertNotFound();
        $this->assertSame(0, Appointment::withoutGlobalScopes()->count());
    }

    public function test_agendamento_online_aparece_na_agenda_da_clinica(): void
    {
        $this->book();
        $admin = User::factory()->for($this->clinic)->admin()->create();

        $this->actingAs($admin)->get('/agenda?date='.$this->day)->assertOk()->assertSee('Maria da Silva');
        $this->actingAs($admin)->get('/personalizar')->assertOk()->assertSee('/agendar/'.$this->clinic->slug);
    }

    public function test_clinica_inativa_nao_tem_pagina_de_agendamento(): void
    {
        $this->clinic->update(['is_active' => false]);
        $this->get('/agendar/'.$this->clinic->slug)->assertNotFound();
    }
}
