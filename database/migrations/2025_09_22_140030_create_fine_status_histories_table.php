<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fine_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fine_id')->constrained('fines')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->string('from_status',30)->nullable();
            $table->string('to_status',30);
            $table->timestamps();
            $table->index(['to_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fine_status_histories');
    }
};

