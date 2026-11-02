<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Professional;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    /** Agenda: visão diária (colunas por profissional) ou semanal. */
    public function index(Request $request)
    {
        $view = $request->query('view') === 'week' ? 'week' : 'day';
        $date = $this->parseDate($request->query('date'));
        $clinic = $this->clinic();

        $professionals = Professional::active()->orderBy('name')->get();
        $selectedPro = $request->integer('professional') ?: null;

        [$from, $to] = $view === 'week'
            ? [$date->copy()->startOfWeek(Carbon::MONDAY), $date->copy()->endOfWeek(Carbon::SUNDAY)]
            : [$date->copy()->startOfDay(), $date->copy()->endOfDay()];

        $appointments = Appointment::with(['patient', 'professional'])
            ->whereBetween('starts_at', [$from, $to])
            ->when($selectedPro, fn ($q) => $q->where('professional_id', $selectedPro))
            ->orderBy('starts_at')
            ->get();

        // Faixa de horários exibida na grade (amplia se houver consulta fora do expediente).
        $openHour = (int) substr($clinic->opens_at, 0, 2);
        $closeHour = (int) ceil(((int) substr($clinic->closes_at, 0, 2) * 60 + (int) substr($clinic->closes_at, 3, 2)) / 60);
        if ($appointments->isNotEmpty()) {
            $openHour = min($openHour, $appointments->min(fn ($a) => $a->starts_at->hour));
            $closeHour = max($closeHour, $appointments->max(fn ($a) => $a->ends_at->hour + ($a->ends_at->minute ? 1 : 0)));
        }

        return view('appointments.index', [
            'view' => $view,
            'date' => $date,
            'from' => $from,
            'to' => $to,
            'appointments' => $appointments,
            'professionals' => $professionals,
            'columns' => $selectedPro ? $professionals->where('id', $selectedPro)->values() : $professionals,
            'selectedPro' => $selectedPro,
            'hours' => range($openHour, max($openHour + 1, $closeHour) - 1),
            'openHour' => $openHour,
        ]);
    }

    public function create(Request $request)
    {
        $clinic = $this->clinic();
        $date = $this->parseDate($request->query('date'));

        $appointment = new Appointment([
            'professional_id' => $request->integer('professional') ?: null,
            'patient_id' => $request->integer('patient') ?: null,
            'type' => 'Consulta',
            'status' => 'scheduled',
        ]);

        return view('appointments.form', [
            'appointment' => $appointment,
            'date' => $date->toDateString(),
            'time' => $request->query('time', substr($clinic->opens_at, 0, 5)),
            'duration' => $clinic->slot_minutes,
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $appointment = Appointment::create($this->validated($request));

        return redirect()
            ->route('appointments.index', ['date' => $appointment->starts_at->toDateString()])
            ->with('success', 'Consulta agendada para '.$appointment->starts_at->translatedFormat('d/m \à\s H:i').'.');
    }

    public function edit(Appointment $appointment)
    {
        return view('appointments.form', [
            'appointment' => $appointment,
            'date' => $appointment->starts_at->toDateString(),
            'time' => $appointment->starts_at->format('H:i'),
            'duration' => $appointment->durationMinutes(),
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $appointment->update($this->validated($request, $appointment));

        return redirect()
            ->route('appointments.index', ['date' => $appointment->starts_at->toDateString()])
            ->with('success', 'Consulta atualizada.');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Appointment::STATUSES))]]);

        if (in_array($data['status'], Appointment::BLOCKING) && ! in_array($appointment->status, Appointment::BLOCKING)
            && Appointment::hasConflict($appointment->professional_id, $appointment->starts_at, $appointment->ends_at, $appointment->id)) {
            return back()->withErrors(['status' => 'Não é possível reativar: o horário já foi ocupado por outra consulta.']);
        }

        $appointment->update($data);

        return back()->with('success', 'Status alterado para "'.$appointment->statusLabel().'".');
    }

    public function destroy(Appointment $appointment)
    {
        $date = $appointment->starts_at->toDateString();
        $appointment->delete();

        return redirect()->route('appointments.index', ['date' => $date])->with('success', 'Agendamento excluído.');
    }

    private function formOptions(): array
    {
        return [
            'patients' => Patient::orderBy('name')->get(['id', 'name', 'phone']),
            'professionals' => Professional::active()->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?Appointment $appointment = null): array
    {
        $clinicId = $request->user()->clinic_id;

        $data = $request->validate([
            'patient_id' => ['required', Rule::exists('patients', 'id')->where('clinic_id', $clinicId)->whereNull('deleted_at')],
            'professional_id' => ['required', Rule::exists('professionals', 'id')->where('clinic_id', $clinicId)],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'duration' => ['required', 'integer', 'min:5', 'max:600'],
            'type' => ['nullable', 'string', 'max:60'],
            'status' => ['required', Rule::in(array_keys(Appointment::STATUSES))],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'patient_id' => 'paciente', 'professional_id' => 'profissional', 'date' => 'data',
            'time' => 'horário', 'duration' => 'duração', 'type' => 'tipo', 'price' => 'valor', 'notes' => 'observações',
        ]);

        $start = Carbon::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['time']);
        $end = $start->copy()->addMinutes((int) $data['duration']);

        if (in_array($data['status'], Appointment::BLOCKING)
            && Appointment::hasConflict((int) $data['professional_id'], $start, $end, $appointment?->id)) {
            throw ValidationException::withMessages([
                'time' => 'Este profissional já tem uma consulta nesse horário.',
            ]);
        }

        return [
            'patient_id' => $data['patient_id'],
            'professional_id' => $data['professional_id'],
            'starts_at' => $start,
            'ends_at' => $end,
            'type' => $data['type'] ?? null,
            'status' => $data['status'],
            'price' => $data['price'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function parseDate(?string $value): Carbon
    {
        try {
            return $value ? Carbon::createFromFormat('Y-m-d', $value)->startOfDay() : Carbon::today();
        } catch (\Throwable) {
            return Carbon::today();
        }
    }
}
