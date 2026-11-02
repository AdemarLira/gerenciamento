<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use Illuminate\Http\Request;

class ProfessionalController extends Controller
{
    public function index()
    {
        $professionals = Professional::withCount([
            'appointments as upcoming_count' => fn ($q) => $q->where('starts_at', '>=', now())->blocking(),
        ])->orderByDesc('is_active')->orderBy('name')->get();

        return view('professionals.index', compact('professionals'));
    }

    public function create()
    {
        return view('professionals.form', ['professional' => new Professional(['color' => '#06B6D4', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        Professional::create($this->validated($request));

        return redirect()->route('professionals.index')->with('success', 'Profissional cadastrado.');
    }

    public function edit(Professional $professional)
    {
        return view('professionals.form', compact('professional'));
    }

    public function update(Request $request, Professional $professional)
    {
        $professional->update($this->validated($request));

        return redirect()->route('professionals.index')->with('success', 'Profissional atualizado.');
    }

    public function destroy(Professional $professional)
    {
        if ($professional->appointments()->exists()) {
            $professional->update(['is_active' => false]);

            return back()->with('success', 'O profissional tem histórico de consultas, por isso foi desativado em vez de excluído.');
        }
        $professional->delete();

        return redirect()->route('professionals.index')->with('success', 'Profissional removido.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'specialty' => ['nullable', 'string', 'max:100'],
            'registry' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:20'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [], ['name' => 'nome', 'specialty' => 'especialidade', 'registry' => 'registro', 'color' => 'cor']);

        $data['is_active'] = $request->boolean('is_active');
        $data['phone'] = preg_replace('/\D/', '', (string) ($data['phone'] ?? '')) ?: null;

        return $data;
    }
}
