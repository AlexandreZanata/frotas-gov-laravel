<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fine_infractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fine_id')->constrained('fines')->cascadeOnDelete();
            $table->string('code',50)->index();
            $table->string('description',255)->nullable();
            $table->decimal('base_amount',12,2)->default(0);
            $table->decimal('extra_fixed',12,2)->default(0);
            $table->decimal('extra_percent',5,2)->default(0); // % sobre base
            $table->decimal('discount_fixed',12,2)->default(0);
            $table->decimal('discount_percent',5,2)->default(0); // % sobre base
            $table->decimal('final_amount',12,2)->default(0);
            $table->date('infraction_date')->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fine_infractions');
    }
};

