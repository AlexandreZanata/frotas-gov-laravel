<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tire_layouts')) {
            Schema::create('tire_layouts', function(Blueprint $table){
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->json('positions'); // [{code,label,x,y}]
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
        Schema::table('vehicle_categories', function(Blueprint $table){
            if (!Schema::hasColumn('vehicle_categories','tire_change_km')) {
                $table->unsignedInteger('tire_change_km')->nullable()->after('oil_change_days');
            }
            if (!Schema::hasColumn('vehicle_categories','tire_change_days')) {
                $table->unsignedInteger('tire_change_days')->nullable()->after('tire_change_km');
            }
            if (!Schema::hasColumn('vehicle_categories','tire_layout_id')) {
                $table->foreignId('tire_layout_id')->nullable()->constrained('tire_layouts')->nullOnDelete()->after('layout_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_categories', function(Blueprint $table){
            if (Schema::hasColumn('vehicle_categories','tire_layout_id')) {
                $table->dropConstrainedForeignId('tire_layout_id');
            }
            foreach(['tire_change_km','tire_change_days'] as $col){
                if (Schema::hasColumn('vehicle_categories',$col)) $table->dropColumn($col);
            }
        });
        Schema::dropIfExists('tire_layouts');
    }
};

