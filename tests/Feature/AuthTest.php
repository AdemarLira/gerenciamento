<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_publicas_carregam(): void
    {
        $this->get('/')->assertOk()->assertSee('Começar teste grátis');
        $this->get('/login')->assertOk();
        $this->get('/cadastro')->assertOk();
    }

    public function test_login_com_marca_da_clinica(): void
    {
        $clinic = Clinic::factory()->create(['name' => 'Clínica Sorriso', 'slug' => 'sorriso', 'welcome_message' => 'Seu sorriso em boas mãos']);

        $this->get('/c/sorriso')->assertOk()->assertSee('Seu sorriso em boas mãos')->assertSee('Clínica Sorriso');
        $this->get('/c/nao-existe')->assertNotFound();

        $clinic->update(['is_active' => false]);
        $this->get('/c/sorriso')->assertNotFound();
    }

    public function test_cadastro_cria_clinica_e_admin(): void
    {
        $this->post('/cadastro', [
            'clinic_name' => 'Clínica Nova Era',
            'name' => 'Maria Souza',
            'email' => 'maria@novaera.com',
            'password' => 'segredo123',
            'password_confirmation' => 'segredo123',
        ])->assertRedirect(route('settings.edit'));

        $user = User::where('email', 'maria@novaera.com')->first();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->isAdmin());
        $this->assertSame('clinica-nova-era', $user->clinic->slug);
    }

    public function test_login_valido_e_invalido(): void
    {
        $user = User::factory()->create(['email' => 'a@a.com']);

        $this->post('/login', ['email' => 'a@a.com', 'password' => 'errada'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => 'a@a.com', 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_clinica_suspensa_nao_acessa(): void
    {
        $user = User::factory()->create();
        $user->clinic->update(['is_active' => false]);

        $this->actingAs($user)->get('/painel')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $this->get('/painel')->assertRedirect('/login');
        $this->get('/pacientes')->assertRedirect('/login');
    }
}
