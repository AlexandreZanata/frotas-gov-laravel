<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehicles', function(Blueprint $table){
            if (!Schema::hasColumn('vehicles','tire_service_base_km')) {
                $table->unsignedBigInteger('tire_service_base_km')->nullable()->after('current_km');
            }
            if (!Schema::hasColumn('vehicles','tire_service_base_date')) {
                $table->date('tire_service_base_date')->nullable()->after('tire_service_base_km');
            }
        });
    }
    public function down(): void
    {
        Schema::table('vehicles', function(Blueprint $table){
            if (Schema::hasColumn('vehicles','tire_service_base_km')) $table->dropColumn('tire_service_base_km');
            if (Schema::hasColumn('vehicles','tire_service_base_date')) $table->dropColumn('tire_service_base_date');
        });
    }
};

