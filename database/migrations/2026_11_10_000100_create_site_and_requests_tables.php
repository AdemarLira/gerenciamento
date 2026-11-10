<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conteúdo do site público de cada clínica (textos, fotos, cores), editável pelo administrador.
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('content')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        // Pedidos de agendamento enviados pelo site, tratados pela equipe.
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('procedure')->nullable();
            $table->date('preferred_date')->nullable();
            $table->string('preferred_period', 20)->nullable(); // manha | tarde | noite
            $table->text('message')->nullable();
            $table->string('status', 20)->default('new');      // new | contacted | scheduled | archived
            $table->timestamps();

            $table->index(['clinic_id', 'status']);
        });

        // Planejamento do procedimento na consulta.
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('procedure')->nullable()->after('type');
            $table->text('procedure_details')->nullable()->after('procedure');
            $table->text('patient_instructions')->nullable()->after('procedure_details');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['procedure', 'procedure_details', 'patient_instructions']);
        });
        Schema::dropIfExists('booking_requests');
        Schema::dropIfExists('sites');
    }
};
