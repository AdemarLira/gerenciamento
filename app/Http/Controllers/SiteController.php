<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Site público da clínica (landing page personalizável) e o formulário
 * "Agendar avaliação", que vira um pedido na caixa de Solicitações do painel.
 */
class SiteController extends Controller
{
    /** Página inicial: o site da clínica principal (SITE_CLINIC no .env ou a primeira clínica ativa). */
    public function home()
    {
        $slug = config('app.site_clinic');
        $clinic = Clinic::where('is_active', true)
            ->when($slug, fn ($q) => $q->where('slug', $slug))
            ->orderBy('id')
            ->first();

        return $clinic ? $this->render($clinic) : view('welcome');
    }

    public function show(string $slug)
    {
        return $this->render($this->findClinic($slug));
    }

    public function request(Request $request, string $slug)
    {
        $clinic = $this->findClinic($slug);

        // Campo invisível contra robôs de spam.
        if (filled($request->input('website'))) {
            return redirect()->route('site.show', $clinic->slug);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:160'],
            'procedure' => ['nullable', 'string', 'max:120'],
            'preferred_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'preferred_period' => ['nullable', 'in:'.implode(',', array_keys(BookingRequest::PERIODS))],
            'message' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'name' => 'nome', 'phone' => 'WhatsApp', 'email' => 'e-mail', 'procedure' => 'tratamento',
            'preferred_date' => 'data de preferência', 'preferred_period' => 'período', 'message' => 'mensagem',
        ]);

        $phone = preg_replace('/\D/', '', $data['phone']);
        if (strlen($phone) < 10 || strlen($phone) > 13) {
            throw ValidationException::withMessages(['phone' => 'Informe um WhatsApp válido com DDD.']);
        }

        $booking = new BookingRequest([...$data, 'name' => trim($data['name']), 'phone' => $phone, 'status' => 'new']);
        $booking->clinic_id = $clinic->id;
        $booking->save();

        // Volta para a página de onde veio (início ou /s/{slug}), nunca para outro domínio.
        $back = strtok(url()->previous(), '#');
        if (! str_starts_with($back, url('/'))) {
            $back = route('site.show', $clinic->slug);
        }

        return redirect()->to($back.'#agendar')->with('site_sent', $booking->firstName());
    }

    private function findClinic(string $slug): Clinic
    {
        return Clinic::where('slug', $slug)->where('is_active', true)->firstOrFail();
    }

    private function render(Clinic $clinic)
    {
        $site = $clinic->siteOrDefault();

        // Site despublicado: só a equipe da clínica consegue ver (para editar com calma).
        abort_unless($site->is_published || auth()->user()?->clinic_id === $clinic->id, 404);

        return view('site.show', ['clinic' => $clinic, 'site' => $site]);
    }
}
