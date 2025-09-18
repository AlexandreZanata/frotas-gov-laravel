<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_fuel_type_id_to_vehicles_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Supondo que a coluna de texto se chame 'fuel_type'
            // A nova coluna será adicionada como nullable temporariamente
            $table->foreignId('fuel_type_id')->nullable()->after('fuel_type')->constrained('fuel_types');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['fuel_type_id']);
            $table->dropColumn('fuel_type_id');
        });
    }
};
