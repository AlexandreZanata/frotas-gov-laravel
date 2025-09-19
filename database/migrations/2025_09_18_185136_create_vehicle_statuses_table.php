// database/migrations/xxxx_xx_xx_xxxxxx_create_vehicle_statuses_table.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
Schema::create('vehicle_statuses', function (Blueprint $table) {
$table->id();
$table->string('name', 50)->unique(); // Ex: 'Disponível'
$table->string('slug', 50)->unique(); // Ex: 'disponivel' (para uso no código)
$table->string('color', 20)->default('secondary'); // Opcional: para usar no frontend (ex: 'success', 'warning', 'danger')
$table->timestamps();
});
}

public function down(): void
{
Schema::dropIfExists('vehicle_statuses');
}
};
