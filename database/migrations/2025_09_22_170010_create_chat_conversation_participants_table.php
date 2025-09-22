<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_conversation_participants', function(Blueprint $table){
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
            $table->unique(['conversation_id','user_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('chat_conversation_participants');
    }
};
