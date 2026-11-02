<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Professional;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();

        $todayAppointments = Appointment::with(['patient', 'professional'])
            ->onDate($today)
            ->orderBy('starts_at')
            ->get();

        $monthQuery = Appointment::whereBetween('starts_at', [$monthStart, $today->copy()->endOfMonth()]);
        $monthPast = (clone $monthQuery)->where('starts_at', '<', now())->whereIn('status', ['completed', 'no_show']);
        $attended = (clone $monthPast)->where('status', 'completed')->count();
        $closed = $monthPast->count();

        // Atendimentos dos últimos 7 dias (para o mini-gráfico)
        $week = collect(range(6, 0))->map(function ($i) use ($today) {
            $day = $today->copy()->subDays($i);

            return [
                'label' => ucfirst($day->translatedFormat('D')),
                'total' => Appointment::onDate($day)->whereNotIn('status', ['canceled'])->count(),
            ];
        });

        return view('dashboard.index', [
            'todayAppointments' => $todayAppointments,
            'nextAppointment' => $todayAppointments->first(fn ($a) => $a->starts_at->isFuture() && in_array($a->status, ['scheduled', 'confirmed'])),
            'stats' => [
                'today' => $todayAppointments->whereNotIn('status', ['canceled'])->count(),
                'today_confirmed' => $todayAppointments->where('status', 'confirmed')->count(),
                'patients' => Patient::count(),
                'patients_month' => Patient::where('created_at', '>=', $monthStart)->count(),
                'attendance_rate' => $closed ? round($attended / $closed * 100) : null,
                'revenue_month' => (float) (clone $monthQuery)->where('status', 'completed')->sum('price'),
            ],
            'week' => $week,
            'professionals' => Professional::active()
                ->withCount(['appointments as today_count' => fn ($q) => $q->onDate($today)->where('status', '!=', 'canceled')])
                ->orderBy('name')->get(),
        ]);
    }
}
