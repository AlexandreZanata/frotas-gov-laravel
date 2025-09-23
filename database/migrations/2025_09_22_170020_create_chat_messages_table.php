<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_messages', function(Blueprint $table){
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type',['text','file','image','audio'])->default('text');
            $table->boolean('is_system')->default(false);
            $table->text('body')->nullable();
            $table->json('attachment_meta')->nullable();
            $table->timestamps();
            $table->index(['conversation_id','created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('chat_messages'); }
};
