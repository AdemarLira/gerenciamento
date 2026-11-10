<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Clinic extends Model
{
    use HasFactory;

    /** Paleta sugerida na tela de personalização. */
    public const PRESET_COLORS = [
        '#7C5CFF' => 'Violeta',
        '#3B82F6' => 'Azul',
        '#06B6D4' => 'Ciano',
        '#10B981' => 'Esmeralda',
        '#84CC16' => 'Lima',
        '#F59E0B' => 'Âmbar',
        '#F97316' => 'Laranja',
        '#EF4444' => 'Vermelho',
        '#EC4899' => 'Rosa',
        '#D4AF37' => 'Dourado',
    ];

    protected $fillable = [
        'name', 'slug', 'document', 'email', 'phone', 'address', 'logo_path',
        'primary_color', 'welcome_message', 'opens_at', 'closes_at', 'slot_minutes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'slot_minutes' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Clinic $clinic) {
            $clinic->slug ??= static::uniqueSlug($clinic->name);
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'clinica';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function professionals(): HasMany
    {
        return $this->hasMany(Professional::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function site(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Site::class);
    }

    /** Site da clínica (cria com o conteúdo padrão na primeira vez). */
    public function siteOrDefault(): Site
    {
        return $this->site ?? $this->setRelation('site', $this->site()->create(['content' => [], 'is_published' => true]))->site;
    }

    public function bookingRequests(): HasMany
    {
        return $this->hasMany(BookingRequest::class);
    }

    public function siteUrl(): string
    {
        return route('site.show', $this->slug);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->filter()->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->implode('');
    }

    /** WhatsApp da clínica (usa o telefone cadastrado em Personalizar). */
    public function whatsappUrl(?string $text = null): ?string
    {
        return \App\Support\WhatsApp::link($this->phone, $text);
    }

    public function bookingUrl(): string
    {
        return route('booking.show', $this->slug);
    }

    /** Variáveis CSS do tema da clínica (cor de destaque e derivadas). */
    public function themeCss(): string
    {
        return \App\Support\Theme::cssVariables($this->primary_color ?: '#7C5CFF');
    }
}
