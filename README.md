# Clinora — Gestão de clínicas e consultórios

Sistema em **Laravel + MySQL** para gerenciar agenda, pacientes, profissionais e equipe de clínicas.
Foi pensado para ser vendido para **várias clínicas em uma única instalação** (modelo SaaS):
cada clínica tem sua conta, seus dados isolados, seu logo, sua cor e seu link de acesso.

## Funcionalidades

- **Site da clínica** (`/` e `/s/{slug}`): landing page de alto padrão (modelo inicial: odontologia estética),
  com animações, tratamentos, antes e depois, jornada, depoimentos, dúvidas e formulário "Agendar avaliação".
  A página inicial mostra o site da clínica definida em `SITE_CLINIC` no `.env` (slug) ou da primeira clínica ativa.
- **Editar site** (`/site`, administrador): troca textos, fotos, logo, paleta de cores, fontes e liga/desliga seções.
  Fotos enviadas ficam em `storage/app/public/sites` (rode `php artisan storage:link`).
- **Solicitações** (`/solicitacoes`): pedidos feitos no site. "Marcar na agenda" cria o paciente e abre a consulta
  já preenchida; a consulta tem procedimento, planejamento clínico e orientações ao paciente (enviáveis pelo WhatsApp).
- **Página de vendas do sistema** (`/plataforma`): landing page para médicos, dentistas, psicólogos e clínicas, com recursos, planos,
  dúvidas frequentes e botão flutuante de WhatsApp. Os planos e preços ficam no topo de `resources/views/welcome.blade.php`.
- **Agendamento online** (`/agendar/{slug}`): o paciente escolhe profissional, dia e horário livre (segunda a sábado,
  dentro do expediente da clínica). O cadastro do paciente é criado pelo WhatsApp informado e a consulta cai direto na agenda.
- **WhatsApp em um clique**: lembrete por consulta no painel, conversa na lista e na ficha do paciente,
  confirmação do paciente após agendar online e botão de suporte no menu.
- **Cadastro de clínica** (`/cadastro`): cria a clínica e o usuário administrador.
- **Login com a marca da clínica** (`/c/{slug}`): link próprio para entregar a cada cliente.
- **Painel**: consultas do dia, próximo atendimento, comparecimento, faturamento do mês e gráfico da semana.
- **Agenda**: visão por dia (colunas por profissional) e por semana, clique no horário vazio para agendar,
  bloqueio automático de conflito de horário, troca rápida de status, lembrete por WhatsApp.
- **Pacientes**: cadastro com CPF/telefone com máscara, convênio, observações, busca e histórico de consultas.
- **Profissionais**: especialidade, registro (CRM/CRO/CRP), cor na agenda, ativo/inativo.
- **Equipe**: usuários com perfil Administrador, Profissional ou Recepção.
- **Personalizar**: nome, logo, cor principal (com prévia ao vivo), frase da tela de login, expediente e duração padrão.

## Requisitos

- PHP 8.2 ou superior (com extensões `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`)
- Composer
- MySQL 8 / MariaDB 10.6+

## Instalação

```bash
composer install
cp .env.example .env          # ajuste DB_DATABASE, DB_USERNAME e DB_PASSWORD
php artisan key:generate
php artisan migrate --seed    # --seed cria uma clínica de demonstração
php artisan storage:link      # necessário para exibir os logos enviados
php artisan serve
```

Acesse `http://localhost:8000`.

Para exibir o seu WhatsApp comercial na página de vendas e no botão de suporte, defina no `.env`:
`SALES_WHATSAPP=11999999999` (DDD + número). Sem essa variável, os botões comerciais ficam ocultos.
O WhatsApp de cada clínica é o telefone cadastrado em **Personalizar**.
Usuário de demonstração: **admin@demo.com** / senha **password** (recepção: recepcao@demo.com).

> Em produção, rode `php artisan migrate --force` sem `--seed` e use `APP_ENV=production` e `APP_DEBUG=false`.
> O seeder usa o Faker, que só é instalado com `composer install` sem `--no-dev`.

## Testes

```bash
php artisan test
```

Os testes cobrem login/cadastro, isolamento de dados entre clínicas, conflito de horários,
permissões por perfil e personalização.

## Como o multi-clínica funciona

- Toda tabela de dados (`patients`, `professionals`, `appointments`) tem a coluna `clinic_id`.
- O trait `App\Models\Concerns\BelongsToClinic` adiciona um filtro global: o usuário logado só enxerga
  registros da própria clínica, e todo registro criado recebe o `clinic_id` dele automaticamente.
- Acessar o ID de um registro de outra clínica retorna 404.
- A cor da clínica vira variáveis CSS (`--accent`) geradas por `App\Support\Theme`; o visual inteiro se adapta.
- Para suspender um cliente (ex.: mensalidade em atraso), marque `clinics.is_active = 0`.
  Para listar os clientes: `php artisan clinicas:listar`.

## Estrutura principal

```
app/Http/Controllers   Auth, Painel, Agenda, Pacientes, Profissionais, Equipe, Personalizar
app/Models             Clinic, User, Patient, Professional, Appointment
database/migrations    Tabelas
public/css/app.css     Tema escuro (sem build: não precisa de Node/Vite)
public/js/app.js       Interações (máscaras, menus, prévia da cor)
resources/views        Telas Blade
```

## Próximos passos sugeridos

- Recuperação de senha por e-mail
- Prontuário/evolução por consulta e anexos (exames)
- Lembretes automáticos (WhatsApp/e-mail) agendados
- Painel do dono do SaaS para gerenciar clínicas e planos
- Agendamento on-line pelo próprio paciente
