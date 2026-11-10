<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Caixa de pedidos de agendamento recebidos pelo site. */
class BookingRequestController extends Controller
{
    public const FILTERS = [
        'abertas' => ['new', 'contacted'],
        'agendadas' => ['scheduled'],
        'arquivadas' => ['archived'],
    ];

    public function index(Request $request)
    {
        $filter = array_key_exists($request->query('filtro'), self::FILTERS) ? $request->query('filtro') : 'abertas';

        return view('requests.index', [
            'filter' => $filter,
            'requests' => BookingRequest::with(['patient', 'appointment.professional'])
                ->whereIn('status', self::FILTERS[$filter])
                ->orderByRaw("CASE WHEN status = 'new' THEN 0 ELSE 1 END")
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'counts' => BookingRequest::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function update(Request $request, BookingRequest $bookingRequest)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['new', 'contacted', 'archived'])]]);
        $bookingRequest->update($data);

        return back()->with('success', 'Solicitação de '.$bookingRequest->firstName().' marcada como "'.$bookingRequest->statusLabel().'".');
    }

    /** Cria (ou encontra) o paciente e abre a agenda já preenchida para marcar a consulta. */
    public function schedule(BookingRequest $bookingRequest)
    {
        $patient = $bookingRequest->patient
            ?? Patient::where('phone', $bookingRequest->phone)->first()
            ?? Patient::create([
                'name' => $bookingRequest->name,
                'phone' => $bookingRequest->phone,
                'email' => $bookingRequest->email,
            ]);

        $bookingRequest->update([
            'patient_id' => $patient->id,
            'status' => $bookingRequest->status === 'new' ? 'contacted' : $bookingRequest->status,
        ]);

        return redirect()->route('appointments.create', array_filter([
            'patient' => $patient->id,
            'solicitacao' => $bookingRequest->id,
            'date' => $bookingRequest->preferred_date?->toDateString(),
        ]));
    }
}
