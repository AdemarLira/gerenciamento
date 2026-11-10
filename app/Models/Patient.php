<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use BelongsToClinic, HasFactory, SoftDeletes;

    public const GENDERS = [
        'feminino' => 'Feminino',
        'masculino' => 'Masculino',
        'outro' => 'Outro',
        'nao_informado' => 'Prefere não informar',
    ];

    protected $fillable = [
        'clinic_id', 'name', 'cpf', 'birth_date', 'gender', 'phone', 'email', 'address', 'insurance', 'notes',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term = trim((string) $term)) {
            return $query;
        }
        $digits = preg_replace('/\D/', '', $term);

        return $query->where(function (Builder $q) use ($term, $digits) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
            if ($digits !== '') {
                $q->orWhere('cpf', 'like', "%{$digits}%")
                    ->orWhere('phone', 'like', "%{$digits}%");
            }
        });
    }

    public function age(): ?int
    {
        return $this->birth_date?->age;
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->filter()->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->implode('');
    }

    public function formattedCpf(): ?string
    {
        $d = preg_replace('/\D/', '', (string) $this->cpf);

        return strlen($d) === 11
            ? substr($d, 0, 3).'.'.substr($d, 3, 3).'.'.substr($d, 6, 3).'-'.substr($d, 9, 2)
            : $this->cpf;
    }

    public function formattedPhone(): ?string
    {
        $d = preg_replace('/\D/', '', (string) $this->phone);

        return match (strlen($d)) {
            11 => sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 5), substr($d, 7)),
            10 => sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 4), substr($d, 6)),
            default => $this->phone,
        };
    }

    public function whatsappUrl(?string $text = null): ?string
    {
        return \App\Support\WhatsApp::link($this->phone, $text);
    }

    public function firstName(): string
    {
        return \Illuminate\Support\Str::of($this->name)->trim()->explode(' ')->first();
    }
}
