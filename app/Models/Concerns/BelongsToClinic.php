<?php

namespace App\Models\Concerns;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Isola os dados por clínica (multi-tenant em banco único).
 *
 * - Toda consulta feita por um usuário logado só enxerga registros da clínica dele.
 * - Todo registro criado recebe automaticamente o clinic_id do usuário logado.
 */
trait BelongsToClinic
{
    public static function bootBelongsToClinic(): void
    {
        static::addGlobalScope('clinic', function (Builder $builder) {
            if ($clinicId = static::currentClinicId()) {
                $builder->where($builder->getModel()->qualifyColumn('clinic_id'), $clinicId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->clinic_id) && $clinicId = static::currentClinicId()) {
                $model->clinic_id = $clinicId;
            }
        });
    }

    protected static function currentClinicId(): ?int
    {
        return Auth::hasUser() ? Auth::user()->clinic_id : null;
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
