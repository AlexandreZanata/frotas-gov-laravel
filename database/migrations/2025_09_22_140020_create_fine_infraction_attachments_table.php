<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fine_infraction_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fine_infraction_id')->constrained('fine_infractions')->cascadeOnDelete();
            $table->string('type',30)->nullable(); // evidencias, boleto, outro
            $table->string('original_name');
            $table->string('path');
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
            $table->index(['type']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('fine_infraction_attachments');
    }
};

