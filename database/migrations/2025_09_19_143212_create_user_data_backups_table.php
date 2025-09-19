<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_data_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('Admin que executou a exclusão')->constrained('users');
            $table->string('deleted_user_name');
            $table->string('deleted_user_email');
            $table->longText('report_summary'); // Resumo dos dados
            $table->binary('pdf_content'); // Alterado de longBlob para binary
            $table->timestamps();
        });

        // Para garantir que seja LONGBLOB no MySQL
        DB::statement('ALTER TABLE user_data_backups MODIFY pdf_content LONGBLOB');
    }

    public function down(): void {
        Schema::dropIfExists('user_data_backups');
    }
};
