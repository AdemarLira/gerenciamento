<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Garante que uma clínica nunca enxerga ou altera dados de outra. */
class ClinicIsolationTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private Patient $patientB;
    private Professional $proB;

    protected function setUp(): void
    {
        parent::setUp();

        $a = Clinic::factory()->create();
        $b = Clinic::factory()->create();
        $this->userA = User::factory()->for($a)->admin()->create();

        Patient::factory()->for($a)->create(['name' => 'Paciente da Clínica A']);
        $this->patientB = Patient::factory()->for($b)->create(['name' => 'Paciente da Clínica B']);
        $this->proB = Professional::factory()->for($b)->create();
    }

    public function test_listagem_mostra_apenas_pacientes_da_propria_clinica(): void
    {
        $this->actingAs($this->userA)->get('/pacientes')
            ->assertOk()
            ->assertSee('Paciente da Clínica A')
            ->assertDontSee('Paciente da Clínica B');
    }

    public function test_nao_acessa_paciente_de_outra_clinica(): void
    {
        $this->actingAs($this->userA)->get("/pacientes/{$this->patientB->id}")->assertNotFound();
        $this->actingAs($this->userA)->put("/pacientes/{$this->patientB->id}", ['name' => 'Hack'])->assertNotFound();
        $this->actingAs($this->userA)->delete("/pacientes/{$this->patientB->id}")->assertNotFound();
        $this->assertSame('Paciente da Clínica B', $this->patientB->fresh()->name);
    }

    public function test_nao_agenda_com_paciente_ou_profissional_de_outra_clinica(): void
    {
        $proA = Professional::factory()->for($this->userA->clinic)->create();

        $this->actingAs($this->userA)->post('/agenda', [
            'patient_id' => $this->patientB->id,
            'professional_id' => $proA->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '10:00',
            'duration' => 30,
            'status' => 'scheduled',
        ])->assertSessionHasErrors('patient_id');

        $this->assertSame(0, Appointment::withoutGlobalScopes()->count());
    }

    public function test_nao_edita_profissional_ou_usuario_de_outra_clinica(): void
    {
        $userB = User::factory()->for($this->proB->clinic)->create();

        $this->actingAs($this->userA)->get("/profissionais/{$this->proB->id}/editar")->assertNotFound();
        $this->actingAs($this->userA)->get("/equipe/{$userB->id}/editar")->assertNotFound();
        $this->actingAs($this->userA)->delete("/equipe/{$userB->id}")->assertNotFound();
    }

    public function test_registros_novos_recebem_a_clinica_do_usuario(): void
    {
        $this->actingAs($this->userA)->post('/pacientes', ['name' => 'Novo Paciente'])->assertRedirect();

        $this->assertSame($this->userA->clinic_id, Patient::withoutGlobalScopes()->where('name', 'Novo Paciente')->value('clinic_id'));
    }

    public function test_cpf_pode_repetir_entre_clinicas_mas_nao_na_mesma(): void
    {
        $this->patientB->update(['cpf' => '12345678901']);

        $this->actingAs($this->userA)->post('/pacientes', ['name' => 'X', 'cpf' => '123.456.789-01'])->assertSessionHasNoErrors();
        $this->actingAs($this->userA)->post('/pacientes', ['name' => 'Y', 'cpf' => '123.456.789-01'])->assertSessionHasErrors('cpf');
    }
}
