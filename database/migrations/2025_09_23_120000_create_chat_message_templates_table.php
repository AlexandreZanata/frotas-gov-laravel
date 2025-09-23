<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_message_templates', function(Blueprint $table){
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->json('style')->nullable(); // {bg,text,border,effect}
            $table->enum('scope',['global','secretariat'])->default('global');
            $table->foreignId('secretariat_id')->nullable()->constrained('secretariats')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['scope','secretariat_id']);
        });
        Schema::table('chat_messages', function(Blueprint $table){
            $table->foreignId('template_id')->nullable()->after('attachment_meta')->constrained('chat_message_templates')->nullOnDelete();
            $table->string('style_class')->nullable()->after('template_id');
        });
    }
    public function down(): void {
        Schema::table('chat_messages', function(Blueprint $table){
            $table->dropConstrainedForeignId('template_id');
            $table->dropColumn('style_class');
        });
        Schema::dropIfExists('chat_message_templates');
    }
};

