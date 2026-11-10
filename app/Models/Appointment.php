<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use BelongsToClinic, HasFactory;

    public const STATUSES = [
        'scheduled' => 'Agendado',
        'confirmed' => 'Confirmado',
        'in_progress' => 'Em atendimento',
        'completed' => 'Concluído',
        'no_show' => 'Faltou',
        'canceled' => 'Cancelado',
    ];

    public const TYPES = ['Consulta', 'Retorno', 'Avaliação', 'Procedimento', 'Exame', 'Sessão'];

    /** Status que ocupam horário na agenda (usados na verificação de conflito). */
    public const BLOCKING = ['scheduled', 'confirmed', 'in_progress', 'completed'];

    protected $fillable = [
        'clinic_id', 'patient_id', 'professional_id', 'starts_at', 'ends_at', 'type', 'status', 'price', 'notes',
        'procedure', 'procedure_details', 'patient_instructions',
    ];

    /** Procedimentos sugeridos no formulário (pode digitar outro). */
    public const PROCEDURES = [
        'Avaliação', 'Lentes de contato dental', 'Facetas em porcelana', 'Implante dentário', 'Coroa sobre implante',
        'Clareamento', 'Alinhadores invisíveis', 'Limpeza / profilaxia', 'Restauração', 'Canal', 'Extração', 'Gengivoplastia',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function scopeOnDate(Builder $query, $date): Builder
    {
        return $query->whereDate('starts_at', $date);
    }

    public function scopeBlocking(Builder $query): Builder
    {
        return $query->whereIn('status', self::BLOCKING);
    }

    /** Existe outro agendamento do mesmo profissional sobrepondo este intervalo? */
    public static function hasConflict(int $professionalId, $start, $end, ?int $ignoreId = null): bool
    {
        return static::query()
            ->where('professional_id', $professionalId)
            ->blocking()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function durationMinutes(): int
    {
        return (int) $this->starts_at->diffInMinutes($this->ends_at);
    }
}
