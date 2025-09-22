<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            // Número do Auto de Infração único (uma multa agrupa diversas infrações)
            $table->string('auto_number')->unique();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->string('auth_code', 40)->unique(); // Código para verificação de autenticidade
            // draft = rascunho antes de notificar condutor / aguardando_pagamento / pago / cancelado / arquivado
            $table->enum('status',[ 'draft','aguardando_pagamento','pago','cancelado','arquivado' ])->default('draft');
            $table->decimal('total_amount',12,2)->default(0);
            $table->timestamp('first_view_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
