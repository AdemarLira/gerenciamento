<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BookingRequest;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinic;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinic = Clinic::factory()->create(['slug' => 'helena']);
        $this->admin = User::factory()->for($this->clinic)->admin()->create();
    }

    private function siteForm(array $over = []): array
    {
        return array_replace_recursive([
            'is_published' => '1',
            'content' => [
                'brand' => ['name' => 'Clínica Sorriso Fino', 'tagline' => 'Odontologia'],
                'hero' => ['title' => 'Sorrisos autorais', 'highlight' => '', 'image' => 'unsplash:1548382131-e0ebb1f0cdea', 'badges' => "Um\nDois"],
                'treatments' => ['items' => [
                    ['title' => 'Facetas', 'text' => 'Porcelana', 'image' => ''],
                    ['title' => '', 'text' => '', 'image' => ''],
                ]],
                'sections' => ['results' => '0', 'faq' => '1'],
                'theme' => ['accent' => '#AA3355', 'font' => 'editorial'],
            ],
        ], $over);
    }

    public function test_pagina_inicial_mostra_o_site_de_exemplo_da_dentista(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Helena Duarte')
            ->assertSee('Lentes de contato dental')
            ->assertSee('Agende sua avaliação')
            ->assertSee('/s/helena/agendar', false);

        $this->get('/s/helena')->assertOk()->assertSeeText('naturalmente seu.');
        $this->get('/plataforma')->assertOk()->assertSee('Começar teste grátis');
    }

    public function test_sem_clinica_a_inicial_mostra_a_pagina_de_vendas(): void
    {
        $this->clinic->update(['is_active' => false]);
        $this->get('/')->assertOk()->assertSee('Começar teste grátis');
    }

    public function test_paciente_solicita_avaliacao_pelo_site(): void
    {
        $this->from('/s/helena')->post('/s/helena/agendar', [
            'name' => 'Marina Costa',
            'phone' => '(11) 98888-7777',
            'procedure' => 'Lentes de contato dental',
            'preferred_date' => now()->addDays(3)->toDateString(),
            'preferred_period' => 'tarde',
            'message' => 'Quero melhorar a cor dos dentes.',
        ])->assertRedirect('/s/helena#agendar')->assertSessionHas('site_sent', 'Marina');

        $req = BookingRequest::withoutGlobalScopes()->firstOrFail();
        $this->assertSame($this->clinic->id, $req->clinic_id);
        $this->assertSame('11988887777', $req->phone);
        $this->assertSame('new', $req->status);

        $this->post('/s/helena/agendar', ['name' => 'X', 'phone' => '11999998888'])->assertSessionHasErrors('name');
        $this->post('/s/helena/agendar', ['name' => 'Ana Lima', 'phone' => '123'])->assertSessionHasErrors('phone');
        $this->assertSame(1, BookingRequest::withoutGlobalScopes()->count());
    }

    public function test_robo_que_preenche_campo_invisivel_e_ignorado(): void
    {
        $this->post('/s/helena/agendar', ['name' => 'Spam Bot', 'phone' => '11999998888', 'website' => 'http://spam'])->assertRedirect();
        $this->assertSame(0, BookingRequest::withoutGlobalScopes()->count());
    }

    public function test_equipe_transforma_solicitacao_em_consulta_com_procedimento(): void
    {
        $pro = Professional::factory()->for($this->clinic)->create();
        $req = BookingRequest::withoutGlobalScopes()->create([
            'clinic_id' => $this->clinic->id, 'name' => 'Marina Costa', 'phone' => '11988887777',
            'procedure' => 'Implante dentário', 'message' => 'Perdi um dente.', 'status' => 'new',
        ]);

        $this->actingAs($this->admin)->get('/solicitacoes')->assertOk()->assertSee('Marina Costa')->assertSee('Marcar na agenda');

        $res = $this->actingAs($this->admin)->post("/solicitacoes/{$req->id}/agendar");
        $patient = Patient::where('phone', '11988887777')->firstOrFail();
        $res->assertRedirect(route('appointments.create', ['patient' => $patient->id, 'solicitacao' => $req->id]));

        $this->actingAs($this->admin)->get(route('appointments.create', ['patient' => $patient->id, 'solicitacao' => $req->id]))
            ->assertOk()->assertSee('Solicitação do site')->assertSee('Implante dentário');

        $this->actingAs($this->admin)->post('/agenda', [
            'patient_id' => $patient->id, 'professional_id' => $pro->id,
            'date' => now()->addDays(2)->toDateString(), 'time' => '10:00', 'duration' => 60, 'status' => 'scheduled',
            'procedure' => 'Implante dentário', 'procedure_details' => 'Elemento 36, guia cirúrgico.',
            'patient_instructions' => 'Jejum de 2 horas.', 'booking_request_id' => $req->id,
        ])->assertRedirect();

        $appt = Appointment::firstOrFail();
        $this->assertSame('Elemento 36, guia cirúrgico.', $appt->procedure_details);
        $this->assertSame('Jejum de 2 horas.', $appt->patient_instructions);
        $this->assertSame('scheduled', $req->fresh()->status);
        $this->assertSame($appt->id, $req->fresh()->appointment_id);

        $this->actingAs($this->admin)->get(route('appointments.edit', $appt))->assertOk()->assertSee('Enviar orientações');
    }

    public function test_solicitacoes_de_outra_clinica_ficam_isoladas(): void
    {
        $other = Clinic::factory()->create();
        $req = BookingRequest::withoutGlobalScopes()->create([
            'clinic_id' => $other->id, 'name' => 'Paciente Alheio', 'phone' => '11977776666', 'status' => 'new',
        ]);

        $this->actingAs($this->admin)->get('/solicitacoes')->assertDontSee('Paciente Alheio');
        $this->actingAs($this->admin)->post("/solicitacoes/{$req->id}/agendar")->assertNotFound();
        $this->actingAs($this->admin)->patch("/solicitacoes/{$req->id}", ['status' => 'archived'])->assertNotFound();
    }

    public function test_admin_edita_textos_cores_fotos_e_secoes_do_site(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->get('/site')->assertOk()->assertSee('Identidade visual');

        $this->actingAs($this->admin)->put('/site', $this->siteForm([
            'uploads' => ['treatments__items__0__image' => UploadedFile::fake()->create('facetas.jpg', 200, 'image/jpeg')],
        ]))->assertRedirect();

        $site = $this->clinic->site()->first();
        $this->assertSame('Clínica Sorriso Fino', $site->get('brand.name'));
        $this->assertSame('', $site->get('hero.highlight'));             // apagado continua vazio
        $this->assertSame(['Um', 'Dois'], $site->get('hero.badges'));
        $this->assertCount(1, $site->get('treatments.items'));           // linha vazia removida
        $this->assertFalse($site->enabled('results'));
        $path = $site->get('treatments.items.0.image');
        Storage::disk('public')->assertExists($path);

        $this->get('/s/helena')->assertOk()
            ->assertSee('Clínica Sorriso Fino')
            ->assertSee('--accent:#AA3355', false)
            ->assertSee('Playfair Display')
            ->assertDontSee('id="resultados"', false);

        // Trocar a foto apaga o arquivo antigo.
        $this->actingAs($this->admin)->put('/site', $this->siteForm(['content' => ['treatments' => ['items' => [['title' => 'Facetas', 'image' => '']]]]]));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_site_despublicado_so_aparece_para_a_equipe(): void
    {
        $this->actingAs($this->admin)->put('/site', $this->siteForm(['is_published' => '0']));
        auth()->logout();

        $this->get('/s/helena')->assertNotFound();
        $this->actingAs($this->admin)->get('/s/helena')->assertOk()->assertSee('Site não publicado');
    }

    public function test_recepcao_nao_edita_o_site(): void
    {
        $reception = User::factory()->for($this->clinic)->create(['role' => 'reception']);
        $this->actingAs($reception)->get('/site')->assertForbidden();
        $this->actingAs($reception)->get('/solicitacoes')->assertOk();
    }

    public function test_restaurar_modelo_padrao(): void
    {
        $this->actingAs($this->admin)->put('/site', $this->siteForm());
        $this->actingAs($this->admin)->post('/site/restaurar')->assertRedirect('/site');

        $this->assertSame('Helena Duarte', $this->clinic->site()->first()->get('brand.name'));
    }

    public function test_imagens_aceitam_unsplash_links_e_arquivos(): void
    {
        $this->assertStringStartsWith('https://images.unsplash.com/photo-abc', Site::imageUrl('unsplash:abc'));
        $this->assertSame('https://x.com/a.jpg', Site::imageUrl('https://x.com/a.jpg'));
        $this->assertNull(Site::imageUrl(''));
        $this->assertStringContainsString('/storage/sites/1/a.jpg', Site::imageUrl('sites/1/a.jpg'));
    }
}
