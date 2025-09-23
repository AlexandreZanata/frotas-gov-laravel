<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fine_view_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fine_id')->constrained('fines')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamp('viewed_at');
            $table->string('ip_address',45)->nullable();
            $table->timestamps();
            $table->index(['viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fine_view_logs');
    }
};

