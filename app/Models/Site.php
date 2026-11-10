<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Site público da clínica. O conteúdo editado fica em `content` (JSON);
 * o que não foi editado usa o modelo padrão de defaults().
 */
class Site extends Model
{
    /** Combinações de fontes disponíveis no editor. */
    public const FONTS = [
        'classico' => ['label' => 'Clássico — Cormorant + Manrope', 'display' => 'Cormorant Garamond', 'body' => 'Manrope',
            'url' => 'family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Manrope:wght@400;500;600;700'],
        'editorial' => ['label' => 'Editorial — Playfair + DM Sans', 'display' => 'Playfair Display', 'body' => 'DM Sans',
            'url' => 'family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&family=DM+Sans:wght@400;500;600;700'],
        'moderno' => ['label' => 'Moderno — Fraunces + Inter', 'display' => 'Fraunces', 'body' => 'Inter',
            'url' => 'family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;1,9..144,400&family=Inter:wght@400;500;600;700'],
    ];

    /** Paletas prontas (cor de destaque, fundo escuro, fundo claro). */
    public const PALETTES = [
        'champagne' => ['label' => 'Champagne', 'accent' => '#B8955A', 'dark' => '#15120F', 'light' => '#F6F1EA'],
        'rose' => ['label' => 'Rosé', 'accent' => '#B97A6F', 'dark' => '#1A1314', 'light' => '#F8F0EE'],
        'sage' => ['label' => 'Sálvia', 'accent' => '#7F9478', 'dark' => '#121612', 'light' => '#F1F3EE'],
        'ocean' => ['label' => 'Oceano', 'accent' => '#4F7C93', 'dark' => '#0E1418', 'light' => '#EEF3F5'],
        'noir' => ['label' => 'Noir', 'accent' => '#C9C2B6', 'dark' => '#0B0B0C', 'light' => '#F3F2EF'],
    ];

    protected $fillable = ['clinic_id', 'content', 'is_published'];

    protected function casts(): array
    {
        return ['content' => 'array', 'is_published' => 'boolean'];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /** Valor do conteúdo (ex.: 'hero.title'), caindo no modelo padrão quando não editado. */
    public function get(string $key, $default = null)
    {
        $value = data_get($this->content ?? [], $key);

        return $value ?? data_get(static::defaults(), $key, $default);
    }

    /** Lista de itens (tratamentos, depoimentos…), sem itens vazios. */
    public function list(string $key): array
    {
        return array_values(array_filter((array) $this->get($key, []), fn ($item) => is_array($item)
            ? collect($item)->filter(fn ($v) => filled($v))->isNotEmpty()
            : filled($item)));
    }

    public function enabled(string $section): bool
    {
        return (bool) $this->get("sections.$section", true);
    }

    /** URL pública de uma imagem: link externo ou arquivo enviado. */
    public static function imageUrl(?string $value, int $width = 1600): ?string
    {
        if (! $value) {
            return null;
        }
        if (str_starts_with($value, 'unsplash:')) {
            return 'https://images.unsplash.com/photo-'.substr($value, 9)."?auto=format&fit=crop&w={$width}&q=80";
        }
        if (preg_match('#^https?://#', $value)) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }

    public function image(string $key, int $width = 1600): ?string
    {
        return static::imageUrl($this->get($key), $width);
    }

    public function font(): array
    {
        return self::FONTS[$this->get('theme.font')] ?? self::FONTS['classico'];
    }

    public function themeCss(): string
    {
        $hex = fn ($v, $fallback) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $v) ? $v : $fallback;
        $accent = $hex($this->get('theme.accent'), '#B8955A');
        [$r, $g, $b] = \App\Support\Theme::rgb($accent);
        $font = $this->font();

        return sprintf(
            '--accent:%s;--accent-rgb:%d,%d,%d;--dark:%s;--light:%s;--font-display:"%s",Georgia,serif;--font-body:"%s",system-ui,sans-serif;',
            $accent, $r, $g, $b,
            $hex($this->get('theme.dark'), '#15120F'),
            $hex($this->get('theme.light'), '#F6F1EA'),
            $font['display'], $font['body']
        );
    }

    /**
     * Modelo inicial: consultório de odontologia estética de alto padrão.
     * Tudo pode ser trocado em "Site" no painel.
     */
    public static function defaults(): array
    {
        return [
            'brand' => [
                'name' => 'Helena Duarte',
                'tagline' => 'Odontologia Estética',
                'logo' => null,
            ],
            'theme' => [
                'palette' => 'champagne',
                'accent' => '#B8955A',
                'dark' => '#15120F',
                'light' => '#F6F1EA',
                'font' => 'classico',
            ],
            'sections' => [
                'treatments' => true, 'about' => true, 'results' => true, 'journey' => true,
                'space' => true, 'testimonials' => true, 'faq' => true,
            ],
            'hero' => [
                'eyebrow' => 'Odontologia estética · Jardins, São Paulo',
                'title' => 'O sorriso que parece',
                'highlight' => 'naturalmente seu.',
                'subtitle' => 'Lentes de contato dental, implantes e reabilitação oral com planejamento digital, atendimento exclusivo e um cuidado artesanal em cada detalhe.',
                'image' => 'unsplash:1548382131-e0ebb1f0cdea',
                'cta' => 'Agendar avaliação',
                'cta_secondary' => 'Conhecer tratamentos',
                'badges' => ['Planejamento digital do sorriso', 'Atendimento com hora marcada', 'Materiais de alta performance'],
            ],
            'marquee' => ['Lentes de contato dental', 'Facetas em porcelana', 'Implantes', 'Clareamento', 'Harmonização do sorriso', 'Alinhadores invisíveis', 'Reabilitação oral'],
            'about' => [
                'title' => 'Cada sorriso é projetado, não padronizado.',
                'name' => 'Dra. Helena Duarte',
                'role' => 'Cirurgiã-dentista · Especialista em Prótese e Estética',
                'registry' => 'CRO-SP 000000',
                'text' => "Há mais de 15 anos dedico minha carreira à odontologia estética com um princípio simples: o resultado precisa combinar com quem você é.\n\nCada caso começa com escuta, fotografia e planejamento digital. Você vê o seu novo sorriso antes de qualquer procedimento — e só seguimos quando ele fizer sentido para você.",
                'image' => 'unsplash:1758600587728-9bde755354ad',
                'stats' => [
                    ['value' => '15+', 'label' => 'anos de experiência'],
                    ['value' => '3.000+', 'label' => 'sorrisos planejados'],
                    ['value' => '100%', 'label' => 'planejamento digital'],
                ],
            ],
            'treatments' => [
                'title' => 'Tratamentos',
                'subtitle' => 'Protocolos individualizados, do primeiro contato ao acompanhamento.',
                'items' => [
                    ['title' => 'Lentes de contato dental', 'text' => 'Laminados ultrafinos em porcelana que corrigem cor, forma e alinhamento com mínimo desgaste.', 'image' => 'unsplash:1677026010083-78ec7f1b84ed'],
                    ['title' => 'Implantes dentários', 'text' => 'Reposição de dentes com cirurgia guiada e coroas sob medida, com função e naturalidade.', 'image' => 'unsplash:1771442873035-474765b40ac6'],
                    ['title' => 'Clareamento', 'text' => 'Protocolos supervisionados para um branco luminoso e compatível com o seu tom de pele.', 'image' => 'unsplash:1567516364473-233c4b6fcfbe'],
                    ['title' => 'Alinhadores invisíveis', 'text' => 'Ortodontia discreta e removível, planejada digitalmente etapa por etapa.', 'image' => 'unsplash:1564420228450-d9a5bc8d6565'],
                    ['title' => 'Planejamento digital', 'text' => 'Escaneamento intraoral e simulação do sorriso antes de iniciar o tratamento.', 'image' => 'unsplash:1600170311833-c2cf5280ce49'],
                ],
            ],
            'results' => [
                'title' => 'Resultados',
                'subtitle' => 'Arraste para comparar. Imagens ilustrativas — substitua pelos casos dos seus pacientes, com autorização.',
                'items' => [
                    ['before' => 'unsplash:1654373535457-383a0a4d00f9', 'after' => 'unsplash:1677026010083-78ec7f1b84ed', 'caption' => 'Lentes de contato em porcelana'],
                    ['before' => 'unsplash:1611695434369-a8f5d76ceb7b', 'after' => 'unsplash:1489278353717-f64c6ee8a4d2', 'caption' => 'Clareamento e recontorno'],
                ],
            ],
            'journey' => [
                'title' => 'Sua jornada até o novo sorriso',
                'steps' => [
                    ['title' => 'Avaliação exclusiva', 'text' => 'Uma conversa sem pressa para entender o que você deseja, com exame clínico completo.'],
                    ['title' => 'Planejamento digital', 'text' => 'Fotos, escaneamento 3D e simulação do resultado. Você aprova o desenho antes de começar.'],
                    ['title' => 'Execução precisa', 'text' => 'Procedimentos em sessões planejadas, com conforto, materiais premium e tecnologia de ponta.'],
                    ['title' => 'Acompanhamento', 'text' => 'Revisões periódicas e um canal direto com a equipe para manter o resultado por muitos anos.'],
                ],
            ],
            'space' => [
                'title' => 'Um espaço pensado para o seu conforto',
                'text' => 'Ambiente reservado, atendimento pontual e tecnologia de ponta em cada sala.',
                'images' => [
                    'unsplash:1728342057953-94bfad8f0e7e',
                    'unsplash:1777331903190-341a3dd0441b',
                    'unsplash:1629909613654-28e377c37b09',
                    'unsplash:1762625570087-6d98fca29531',
                ],
            ],
            'testimonials' => [
                'title' => 'Quem viveu a experiência',
                'items' => [
                    ['name' => 'Mariana C.', 'detail' => 'Lentes de contato', 'text' => 'Ver a simulação antes de começar me deu total segurança. O resultado ficou exatamente como planejamos — natural, do jeito que eu queria.'],
                    ['name' => 'Ricardo A.', 'detail' => 'Implantes', 'text' => 'Atendimento impecável do começo ao fim. Pontualidade, explicações claras e uma equipe que realmente se importa.'],
                    ['name' => 'Fernanda L.', 'detail' => 'Clareamento', 'text' => 'Me senti acolhida desde o primeiro contato pelo WhatsApp. O consultório é lindo e a experiência é de outro nível.'],
                    ['name' => 'Paulo M.', 'detail' => 'Reabilitação oral', 'text' => 'Voltei a sorrir sem receio. Cada etapa foi explicada e cumprida no prazo combinado.'],
                ],
            ],
            'faq' => [
                'title' => 'Perguntas frequentes',
                'items' => [
                    ['q' => 'Como funciona a primeira consulta?', 'a' => 'A avaliação dura cerca de uma hora: conversamos sobre seus objetivos, fazemos o exame clínico e as fotos para o planejamento. Você sai com um plano de tratamento claro.'],
                    ['q' => 'As lentes de contato desgastam os dentes?', 'a' => 'Na maioria dos casos o desgaste é mínimo ou inexistente. A indicação é sempre individual e definida no planejamento digital.'],
                    ['q' => 'Quanto tempo dura o tratamento?', 'a' => 'Depende do caso. Lentes de contato costumam ser concluídas em poucas semanas; implantes seguem o tempo de cicatrização. O cronograma é apresentado antes de começar.'],
                    ['q' => 'Vocês atendem convênio?', 'a' => 'O atendimento é particular. Oferecemos condições de pagamento facilitadas, apresentadas junto com o plano de tratamento.'],
                ],
            ],
            'cta' => [
                'title' => 'Seu novo sorriso começa com uma conversa.',
                'text' => 'Agende sua avaliação. Nossa equipe retorna pelo WhatsApp para confirmar o melhor horário.',
            ],
            'contact' => [
                'whatsapp' => '11999990000',
                'phone' => '(11) 3000-0000',
                'email' => 'contato@helenaduarte.com.br',
                'address' => 'Rua Oscar Freire, 000 — Jardins, São Paulo/SP',
                'hours' => 'Segunda a sexta, 8h às 20h · Sábado, 8h às 13h',
                'instagram' => 'helenaduarte.odonto',
                'map_url' => 'https://maps.google.com/?q=Rua+Oscar+Freire+Jardins+Sao+Paulo',
                'procedures' => ['Avaliação', 'Lentes de contato dental', 'Implantes', 'Clareamento', 'Alinhadores invisíveis', 'Outro'],
            ],
            'seo' => [
                'title' => 'Dra. Helena Duarte — Odontologia Estética em São Paulo',
                'description' => 'Lentes de contato dental, implantes e clareamento com planejamento digital do sorriso. Agende sua avaliação.',
            ],
        ];
    }
}
