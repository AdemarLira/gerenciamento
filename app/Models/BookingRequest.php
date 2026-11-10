<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pedido de agendamento feito pelo paciente no site. */
class BookingRequest extends Model
{
    use BelongsToClinic;

    public const STATUSES = [
        'new' => 'Novo',
        'contacted' => 'Em contato',
        'scheduled' => 'Agendado',
        'archived' => 'Arquivado',
    ];

    public const PERIODS = [
        'manha' => 'Manhã',
        'tarde' => 'Tarde',
        'noite' => 'Noite',
    ];

    protected $fillable = [
        'clinic_id', 'patient_id', 'appointment_id', 'name', 'phone', 'email', 'procedure',
        'preferred_date', 'preferred_period', 'message', 'status',
    ];

    protected function casts(): array
    {
        return ['preferred_date' => 'date'];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['new', 'contacted']);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function periodLabel(): ?string
    {
        return self::PERIODS[$this->preferred_period] ?? null;
    }

    public function firstName(): string
    {
        return \Illuminate\Support\Str::of($this->name)->trim()->explode(' ')->first();
    }

    public function whatsappUrl(?string $text = null): ?string
    {
        return \App\Support\WhatsApp::link($this->phone, $text);
    }

    public function formattedPhone(): string
    {
        return (new Patient(['phone' => $this->phone]))->formattedPhone() ?? $this->phone;
    }
}
