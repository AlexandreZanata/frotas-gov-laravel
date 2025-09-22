<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Adiciona colunas auxiliares
        Schema::table('runs', function(Blueprint $table){
            if(!Schema::hasColumn('runs','blocked_at')){
                $table->dateTime('blocked_at')->nullable()->after('status');
            }
            if(!Schema::hasColumn('runs','blocked_by')){
                $table->foreignId('blocked_by')->nullable()->after('blocked_at')->constrained('users')->nullOnDelete();
            }
            if(!Schema::hasColumn('runs','blocked_previous_status')){
                $table->string('blocked_previous_status',30)->nullable()->after('blocked_by');
            }
        });
        // Ajusta enum para incluir 'blocked'
        // MySQL: modificar coluna status adicionando novo valor
        $driver = DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE runs MODIFY COLUMN status ENUM('pending_start','in_progress','completed','blocked') NOT NULL DEFAULT 'pending_start'");
        } else {
            // Em outros drivers (ex: sqlite) ignoramos pois enum é tratado como texto
        }
    }
    public function down(): void
    {
        // Reverte enum (cuidado: se houver registros 'blocked' podem causar erro). Mantemos colunas.
        $driver = DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE runs MODIFY COLUMN status ENUM('pending_start','in_progress','completed') NOT NULL DEFAULT 'pending_start'");
        }
        Schema::table('runs', function(Blueprint $table){
            if (Schema::hasColumn('runs','blocked_at')) $table->dropColumn('blocked_at');
            if (Schema::hasColumn('runs','blocked_by')) { $table->dropForeign(['blocked_by']); $table->dropColumn('blocked_by'); }
            if (Schema::hasColumn('runs','blocked_previous_status')) $table->dropColumn('blocked_previous_status');
        });
    }
};
