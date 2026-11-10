<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Editor do site público: textos, fotos, cores, fontes e seções. */
class SiteEditorController extends Controller
{
    /** Listas editadas como "um item por linha". */
    private const LINE_LISTS = ['marquee', 'contact.procedures', 'hero.badges'];

    public function edit()
    {
        $clinic = $this->clinic();

        return view('site.edit', [
            'clinic' => $clinic,
            'site' => $clinic->siteOrDefault(),
        ]);
    }

    public function update(Request $request)
    {
        $site = $this->clinic()->siteOrDefault();

        $request->validate([
            'content' => ['required', 'array'],
            'uploads' => ['nullable', 'array'],
            'uploads.*' => ['image', 'mimes:jpg,jpeg,png,webp,avif,svg', 'max:6144'],
        ], [
            'uploads.*.image' => 'Envie apenas imagens (JPG, PNG, WEBP).',
            'uploads.*.max' => 'Cada imagem pode ter no máximo 6 MB.',
        ]);

        $old = $site->content ?? [];
        $content = $this->clean((array) $request->input('content'));

        foreach (self::LINE_LISTS as $key) {
            if (is_string($text = data_get($content, $key))) {
                data_set($content, $key, array_values(array_filter(array_map('trim', preg_split('/\R/', $text)))));
            }
        }

        // Fotos enviadas: o nome do campo é o caminho no conteúdo (ex.: hero__image, treatments__items__3__image).
        foreach ((array) $request->file('uploads', []) as $key => $file) {
            $path = str_replace('__', '.', $key);
            if (! $this->validPath($path)) {
                continue;
            }
            data_set($content, $path, $file->store('sites/'.$site->clinic_id, 'public'));
        }

        // Listas: remove linhas vazias e renumera.
        foreach (['treatments.items', 'results.items', 'journey.steps', 'testimonials.items', 'faq.items', 'about.stats', 'space.images'] as $key) {
            if (is_array($list = data_get($content, $key))) {
                data_set($content, $key, array_values(array_filter($list, fn ($i) => is_array($i)
                    ? collect($i)->filter(fn ($v) => filled($v))->isNotEmpty()
                    : filled($i))));
            }
        }

        foreach (array_keys(Site::defaults()['sections']) as $section) {
            data_set($content, "sections.$section", (bool) data_get($content, "sections.$section"));
        }

        if (! data_get($content, 'brand.name')) {
            throw ValidationException::withMessages(['content.brand.name' => 'Informe o nome que aparece no site.']);
        }

        $site->update([
            'content' => $content,
            'is_published' => $request->boolean('is_published'),
        ]);

        $this->deleteUnusedUploads($old, $content);

        return redirect()->route('site.edit', ['secao' => $request->input('_section')])
            ->with('success', 'Site atualizado. As alterações já estão no ar'.($site->is_published ? '.' : ' (site não publicado).'));
    }

    /** Volta todo o conteúdo para o modelo padrão. */
    public function reset()
    {
        $site = $this->clinic()->siteOrDefault();
        $old = $site->content ?? [];
        $site->update(['content' => []]);
        $this->deleteUnusedUploads($old, []);

        return redirect()->route('site.edit')->with('success', 'O site voltou para o modelo padrão.');
    }

    /** Mantém só seções conhecidas e textos de tamanho razoável. */
    private function clean(array $input): array
    {
        $allowed = array_keys(Site::defaults());
        $input = Arr::only($input, $allowed);

        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                // Campo apagado fica vazio ('') — não volta para o texto padrão.
                $value = Str::limit(trim(str_replace("\r\n", "\n", $value)), 5000, '');
            }
        });

        return $input;
    }

    private function validPath(string $path): bool
    {
        return in_array(explode('.', $path)[0], array_keys(Site::defaults()), true)
            && preg_match('/^[a-z_]+(\.[a-z_0-9]+){1,3}$/', $path);
    }

    /** Apaga do disco as fotos enviadas que deixaram de ser usadas. */
    private function deleteUnusedUploads(array $old, array $new): void
    {
        $files = fn (array $c) => collect(Arr::dot($c))
            ->filter(fn ($v) => is_string($v) && str_starts_with($v, 'sites/'))
            ->values();

        $files($old)->diff($files($new))->each(fn ($path) => Storage::disk('public')->delete($path));
    }
}
