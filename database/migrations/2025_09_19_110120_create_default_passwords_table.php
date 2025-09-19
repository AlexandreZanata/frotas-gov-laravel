<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('default_passwords', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome de identificação da senha, ex: Padrão Sorriso');
            $table->string('password_plain')->comment('A senha em texto plano, que será encriptada ao salvar');
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->constrained('users')->comment('Administrador que criou a senha');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('default_passwords');
    }
};
