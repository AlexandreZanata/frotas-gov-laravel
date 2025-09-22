<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_conversations', function(Blueprint $table){
            $table->id();
            $table->string('title',150)->nullable();
            $table->boolean('is_group')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['is_group']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('chat_conversations');
    }
};
