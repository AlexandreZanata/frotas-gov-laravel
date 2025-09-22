<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Schema;

class OilProduct extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name','code','brand','viscosity','stock_quantity','reorder_level','unit_cost',
        'recommended_interval_km','recommended_interval_days','description'
    ];

    public function oilChangeLogs()
    {
        return $this->hasMany(OilChangeLog::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(OilStockAdjustment::class);
    }

    public function isLowStock(): bool
    {
        // Se colunas não existem, não gerar alerta
        if (!Schema::hasTable($this->getTable()) || !Schema::hasColumn($this->getTable(),'stock_quantity') || !Schema::hasColumn($this->getTable(),'reorder_level')) {
            return false;
        }
        $sq = (int)($this->stock_quantity ?? 0);
        $rl = (int)($this->reorder_level ?? 0);
        return $sq <= $rl;
    }

    public function getDisplayNameAttribute(): string
    {
        if (Schema::hasColumn($this->getTable(),'name') && $this->name) {
            return $this->name;
        }
        if (Schema::hasColumn($this->getTable(),'code') && $this->code) {
            return $this->code;
        }
        return 'Produto #'.$this->id;
    }
}
