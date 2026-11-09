<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Professional;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Agendamento online: página pública em que o paciente escolhe
 * profissional, dia e horário livre da clínica (/agendar/{slug}).
 *
 * As consultas são públicas, então o filtro por clínica é sempre explícito
 * (o escopo global só existe para usuários logados).
 */
class BookingController extends Controller
{
    /** Quantos dias à frente o paciente pode agendar. */
    public const DAYS_AHEAD = 30;

    /** Antecedência mínima (minutos) para um horário de hoje aparecer. */
    public const MIN_NOTICE = 60;

    public function show(Request $request, string $slug)
    {
        $clinic = $this->findClinic($slug);
        $professionals = $this->professionals($clinic);

        $pro = $professionals->firstWhere('id', $request->integer('profissional'))
            ?? ($professionals->count() === 1 ? $professionals->first() : null);

        $days = $this->bookableDays();
        $date = $this->pickDate($request->query('data'), $days);
        $slots = $pro ? $this->availableSlots($clinic, $pro, $date) : collect();

        return view('booking.show', [
            'brand' => $clinic,
            'clinic' => $clinic,
            'professionals' => $professionals,
            'pro' => $pro,
            'days' => $days,
            'date' => $date,
            'slots' => $slots,
            'booked' => session('booked'),
        ]);
    }

    public function store(Request $request, string $slug)
    {
        $clinic = $this->findClinic($slug);

        $data = $request->validate([
            'professional_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:160'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'time.required' => 'Escolha um horário.',
        ], [
            'name' => 'nome', 'phone' => 'WhatsApp', 'email' => 'e-mail', 'notes' => 'observações',
            'date' => 'data', 'time' => 'horário', 'professional_id' => 'profissional',
        ]);

        $phone = preg_replace('/\D/', '', $data['phone']);
        if (strlen($phone) < 10 || strlen($phone) > 13) {
            throw ValidationException::withMessages(['phone' => 'Informe um WhatsApp válido com DDD.']);
        }

        $pro = $this->professionals($clinic)->firstWhere('id', (int) $data['professional_id']);
        abort_unless($pro, 404);

        $date = Carbon::createFromFormat('Y-m-d', $data['date'])->startOfDay();
        $start = $date->copy()->setTimeFromTimeString($data['time']);

        $appointment = DB::transaction(function () use ($clinic, $pro, $date, $start, $data, $phone) {
            // Trava a agenda do profissional para evitar dois pacientes no mesmo horário.
            Professional::withoutGlobalScopes()->whereKey($pro->id)->lockForUpdate()->first();

            if (! $this->availableSlots($clinic, $pro, $date)->contains($start->format('H:i'))) {
                throw ValidationException::withMessages([
                    'time' => 'Esse horário acabou de ser ocupado. Por favor, escolha outro.',
                ]);
            }

            $patient = Patient::withoutGlobalScopes()
                ->where('clinic_id', $clinic->id)
                ->whereNull('deleted_at')
                ->where('phone', $phone)
                ->first();

            if (! $patient) {
                $patient = new Patient(['name' => trim($data['name']), 'phone' => $phone, 'email' => $data['email'] ?? null]);
                $patient->clinic_id = $clinic->id;
                $patient->save();
            } elseif (! $patient->email && ! empty($data['email'])) {
                $patient->update(['email' => $data['email']]);
            }

            $appointment = new Appointment([
                'patient_id' => $patient->id,
                'professional_id' => $pro->id,
                'starts_at' => $start,
                'ends_at' => $start->copy()->addMinutes($clinic->slot_minutes),
                'type' => 'Consulta',
                'status' => 'scheduled',
                'notes' => trim('Agendado online pelo paciente. '.($data['notes'] ?? '')),
            ]);
            $appointment->clinic_id = $clinic->id;
            $appointment->save();

            return $appointment;
        });

        return redirect()
            ->route('booking.show', $clinic->slug)
            ->with('booked', [
                'name' => trim($data['name']),
                'professional' => $pro->name,
                'specialty' => $pro->specialty,
                'starts_at' => $appointment->starts_at->toIso8601String(),
            ]);
    }

    private function findClinic(string $slug): Clinic
    {
        return Clinic::where('slug', $slug)->where('is_active', true)->firstOrFail();
    }

    private function professionals(Clinic $clinic): Collection
    {
        return Professional::withoutGlobalScopes()
            ->where('clinic_id', $clinic->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /** Próximos dias com atendimento (segunda a sábado). */
    private function bookableDays(): Collection
    {
        return collect(range(0, self::DAYS_AHEAD))
            ->map(fn ($i) => Carbon::today()->addDays($i))
            ->reject(fn (Carbon $d) => $d->isSunday())
            ->values();
    }

    private function pickDate(?string $value, Collection $days): Carbon
    {
        try {
            $date = $value ? Carbon::createFromFormat('Y-m-d', $value)->startOfDay() : null;
        } catch (\Throwable) {
            $date = null;
        }

        return $date && $days->contains(fn ($d) => $d->isSameDay($date)) ? $date : $days->first();
    }

    /**
     * Horários livres do profissional no dia, dentro do expediente da clínica.
     *
     * @return Collection<int, string> horários no formato H:i
     */
    public function availableSlots(Clinic $clinic, Professional $pro, Carbon $date): Collection
    {
        if ($date->isSunday() || $date->lt(Carbon::today()) || $date->gt(Carbon::today()->addDays(self::DAYS_AHEAD))) {
            return collect();
        }

        $step = max(5, (int) $clinic->slot_minutes);
        $open = $date->copy()->setTimeFromTimeString(substr($clinic->opens_at, 0, 5));
        $close = $date->copy()->setTimeFromTimeString(substr($clinic->closes_at, 0, 5));
        $earliest = now()->addMinutes(self::MIN_NOTICE);

        $busy = Appointment::withoutGlobalScopes()
            ->where('clinic_id', $clinic->id)
            ->where('professional_id', $pro->id)
            ->whereIn('status', Appointment::BLOCKING)
            ->where('starts_at', '<', $close)
            ->where('ends_at', '>', $open)
            ->get(['starts_at', 'ends_at']);

        $slots = collect();
        for ($t = $open->copy(); $t->copy()->addMinutes($step)->lte($close); $t->addMinutes($step)) {
            $end = $t->copy()->addMinutes($step);
            if ($t->lt($earliest)) {
                continue;
            }
            $taken = $busy->contains(fn ($a) => $a->starts_at->lt($end) && $a->ends_at->gt($t));
            if (! $taken) {
                $slots->push($t->format('H:i'));
            }
        }

        return $slots;
    }
}
