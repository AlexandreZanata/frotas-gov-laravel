<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('cpf', 17)->unique()->nullable(); // nullable para testes genéricos
            $table->string('email', 100)->unique();
            $table->string('password'); // 255 caracteres por padrão

            // Chaves Estrangeiras (Relacionamentos)
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('secretariat_id')->nullable()->constrained('secretariats')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');

            // Campos Adicionais do Frotas Gov
            $table->string('cnh_number', 20)->nullable();
            $table->date('cnh_expiry_date')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('cnh_photo_path')->nullable();
            $table->string('phone', 25)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps(); // Cria as colunas created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
