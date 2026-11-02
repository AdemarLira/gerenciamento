<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('clinicas:listar', function () {
    $this->table(
        ['ID', 'Nome', 'Slug', 'Usuários', 'Ativa'],
        \App\Models\Clinic::withCount('users')->get()
            ->map(fn ($c) => [$c->id, $c->name, $c->slug, $c->users_count, $c->is_active ? 'sim' : 'não'])
    );
})->purpose('Lista as clínicas cadastradas no sistema');
