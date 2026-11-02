<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $patients = Patient::query()
            ->search($request->query('q'))
            ->withCount('appointments')
            ->withMax('appointments as last_visit', 'starts_at')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', ['patients' => $patients, 'q' => $request->query('q')]);
    }

    public function create()
    {
        return view('patients.form', ['patient' => new Patient]);
    }

    public function store(Request $request)
    {
        $patient = Patient::create($this->validated($request));

        return redirect()->route('patients.show', $patient)->with('success', 'Paciente cadastrado com sucesso.');
    }

    public function show(Patient $patient)
    {
        $appointments = $patient->appointments()->with('professional')->latest('starts_at')->get();

        return view('patients.show', [
            'patient' => $patient,
            'upcoming' => $appointments->filter(fn ($a) => $a->starts_at->isFuture())->sortBy('starts_at'),
            'history' => $appointments->filter(fn ($a) => $a->starts_at->isPast()),
        ]);
    }

    public function edit(Patient $patient)
    {
        return view('patients.form', ['patient' => $patient]);
    }

    public function update(Request $request, Patient $patient)
    {
        $patient->update($this->validated($request, $patient));

        return redirect()->route('patients.show', $patient)->with('success', 'Dados do paciente atualizados.');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('patients.index')->with('success', 'Paciente removido.');
    }

    private function validated(Request $request, ?Patient $patient = null): array
    {
        $request->merge([
            'cpf' => preg_replace('/\D/', '', (string) $request->input('cpf')) ?: null,
            'phone' => preg_replace('/\D/', '', (string) $request->input('phone')) ?: null,
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'cpf' => [
                'nullable', 'digits:11',
                Rule::unique('patients', 'cpf')
                    ->where('clinic_id', $request->user()->clinic_id)
                    ->ignore($patient?->id),
            ],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', Rule::in(array_keys(Patient::GENDERS))],
            'phone' => ['nullable', 'string', 'min:10', 'max:13'],
            'email' => ['nullable', 'email', 'max:160'],
            'address' => ['nullable', 'string', 'max:255'],
            'insurance' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'name' => 'nome', 'birth_date' => 'data de nascimento', 'phone' => 'telefone',
            'gender' => 'gênero', 'address' => 'endereço', 'insurance' => 'convênio', 'notes' => 'observações',
        ]);
    }
}
