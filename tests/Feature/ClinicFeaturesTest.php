<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClinicFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Professional $pro;
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $clinic = Clinic::factory()->create();
        $this->admin = User::factory()->for($clinic)->admin()->create();
        $this->pro = Professional::factory()->for($clinic)->create();
        $this->patient = Patient::factory()->for($clinic)->create();
    }

    private function book(array $over = [])
    {
        return $this->actingAs($this->admin)->post('/agenda', array_merge([
            'patient_id' => $this->patient->id,
            'professional_id' => $this->pro->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '10:00',
            'duration' => 30,
            'status' => 'scheduled',
            'type' => 'Consulta',
        ], $over));
    }

    public function test_todas_as_telas_carregam(): void
    {
        Appointment::factory()->create([
            'clinic_id' => $this->admin->clinic_id, 'patient_id' => $this->patient->id, 'professional_id' => $this->pro->id,
            'starts_at' => today()->setTime(9, 0), 'ends_at' => today()->setTime(9, 30),
        ]);
        $a = Appointment::first();

        foreach (['/painel', '/agenda', '/agenda?view=week', '/agenda/nova', "/agenda/{$a->id}/editar",
                  '/pacientes', '/pacientes/novo', "/pacientes/{$this->patient->id}", "/pacientes/{$this->patient->id}/editar",
                  '/profissionais', '/profissionais/novo', "/profissionais/{$this->pro->id}/editar",
                  '/equipe', '/equipe/novo', '/personalizar'] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_agendamento_e_bloqueio_de_conflito(): void
    {
        $this->book()->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(1, Appointment::count());

        // Mesmo profissional, horário sobreposto → bloqueado
        $this->book(['time' => '10:15'])->assertSessionHasErrors('time');
        // Logo após → permitido
        $this->book(['time' => '10:30'])->assertSessionHasNoErrors();
        // Outro profissional no mesmo horário → permitido
        $other = Professional::factory()->for($this->admin->clinic)->create();
        $this->book(['professional_id' => $other->id])->assertSessionHasNoErrors();

        $this->assertSame(3, Appointment::count());
    }

    public function test_cancelamento_libera_horario_e_reativacao_respeita_conflito(): void
    {
        $this->book();
        $first = Appointment::first();
        $this->actingAs($this->admin)->patch("/agenda/{$first->id}/status", ['status' => 'canceled'])->assertSessionHasNoErrors();

        $this->book()->assertSessionHasNoErrors();

        $this->actingAs($this->admin)->patch("/agenda/{$first->id}/status", ['status' => 'confirmed'])->assertSessionHasErrors('status');
        $this->assertSame('canceled', $first->fresh()->status);
    }

    public function test_recepcao_nao_acessa_area_administrativa(): void
    {
        $reception = User::factory()->for($this->admin->clinic)->create(['role' => 'reception']);

        $this->actingAs($reception)->get('/personalizar')->assertForbidden();
        $this->actingAs($reception)->get('/equipe')->assertForbidden();
        $this->actingAs($reception)->get('/profissionais')->assertForbidden();
        $this->actingAs($reception)->get('/agenda')->assertOk();
        $this->actingAs($reception)->get('/pacientes')->assertOk();
    }

    public function test_personalizacao_salva_cor_logo_e_aplica_tema(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->put('/personalizar', [
            'name' => 'Clínica Personalizada',
            'slug' => 'personalizada',
            'primary_color' => '#10b981',
            'opens_at' => '07:00',
            'closes_at' => '19:00',
            'slot_minutes' => 45,
            'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $clinic = $this->admin->clinic->fresh();
        $this->assertSame('#10B981', $clinic->primary_color);
        $this->assertSame(45, $clinic->slot_minutes);
        Storage::disk('public')->assertExists($clinic->logo_path);

        $this->actingAs($this->admin)->get('/painel')->assertSee('--accent:#10B981', false)->assertSee('Clínica Personalizada');
    }

    public function test_paciente_crud(): void
    {
        $this->actingAs($this->admin)->post('/pacientes', [
            'name' => 'João da Silva', 'cpf' => '111.222.333-44', 'phone' => '(11) 98888-7777', 'birth_date' => '1990-05-10',
        ])->assertRedirect();

        $p = Patient::where('name', 'João da Silva')->first();
        $this->assertSame('11122233344', $p->cpf);
        $this->assertSame('(11) 98888-7777', $p->formattedPhone());

        $this->actingAs($this->admin)->get('/pacientes?q=joão')->assertSee('João da Silva');
        $this->actingAs($this->admin)->get('/pacientes?q=111222')->assertSee('João da Silva');

        $this->actingAs($this->admin)->delete("/pacientes/{$p->id}")->assertRedirect('/pacientes');
        $this->assertSoftDeleted($p);
    }

    public function test_admin_nao_remove_o_proprio_acesso(): void
    {
        $this->actingAs($this->admin)->put("/equipe/{$this->admin->id}", [
            'name' => $this->admin->name, 'email' => $this->admin->email, 'role' => 'reception',
        ])->assertSessionHasErrors('role');
        $this->assertTrue($this->admin->fresh()->isAdmin());
    }
}
