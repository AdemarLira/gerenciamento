<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Personalização da clínica: identidade visual, contato e expediente. */
class SettingsController extends Controller
{
    public function edit()
    {
        return view('settings.edit', [
            'clinic' => $this->clinic(),
            'presets' => Clinic::PRESET_COLORS,
        ]);
    }

    public function update(Request $request)
    {
        $clinic = $this->clinic();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:60', Rule::unique('clinics', 'slug')->ignore($clinic->id)],
            'document' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'welcome_message' => ['nullable', 'string', 'max:160'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'opens_at' => ['required', 'date_format:H:i'],
            'closes_at' => ['required', 'date_format:H:i', 'after:opens_at'],
            'slot_minutes' => ['required', 'integer', Rule::in([10, 15, 20, 30, 40, 45, 50, 60, 90])],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ], [], [
            'name' => 'nome', 'slug' => 'endereço do link', 'primary_color' => 'cor principal',
            'opens_at' => 'abertura', 'closes_at' => 'fechamento', 'slot_minutes' => 'duração padrão',
        ]);

        if ($request->boolean('remove_logo') && $clinic->logo_path) {
            Storage::disk('public')->delete($clinic->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($clinic->logo_path) {
                Storage::disk('public')->delete($clinic->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store("clinics/{$clinic->id}", 'public');
        }

        unset($data['logo'], $data['remove_logo']);
        $data['slug'] = strtolower($data['slug']);
        $data['primary_color'] = strtoupper($data['primary_color']);

        $clinic->update($data);

        return redirect()->route('settings.edit')->with('success', 'Personalização salva.');
    }
}
