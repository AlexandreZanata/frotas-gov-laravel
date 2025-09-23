<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;
use Illuminate\Support\Str;
use App\Models\{FineInfraction, FineStatusHistory, FineViewLog};

class Fine extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'auto_number','vehicle_id','driver_id','auth_code','status','total_amount','first_view_at','acknowledged_at','paid_at','notes'
    ];

    protected $casts = [
        'first_view_at'=>'datetime',
        'acknowledged_at'=>'datetime',
        'paid_at'=>'datetime',
        'total_amount'=>'float'
    ];

    protected static function booted(): void
    {
        static::creating(function(Fine $fine){
            if (empty($fine->auth_code)) {
                $fine->auth_code = Str::random(32);
            }
        });
        static::saved(function(Fine $fine){
            // Mantém total consistente
            $fine->recalculateTotal(false);
        });
    }

    public function vehicle(){ return $this->belongsTo(Vehicle::class); }
    public function driver(){ return $this->belongsTo(User::class,'driver_id'); }
    public function infractions(){ return $this->hasMany(FineInfraction::class); }
    public function statusHistories(){ return $this->hasMany(FineStatusHistory::class); }
    public function viewLogs(){ return $this->hasMany(FineViewLog::class); }

    public function scopePendingAcknowledgement($q, $userId){
        return $q->where('driver_id',$userId)
                 ->whereNull('acknowledged_at')
                 ->whereIn('status',['aguardando_pagamento']);
    }

    public function recalculateTotal(bool $persist = true): float
    {
        $total = (float)$this->infractions()->sum('final_amount');
        if ($persist && $this->total_amount !== $total) {
            $this->total_amount = $total; $this->saveQuietly();
        } else {
            $this->total_amount = $total; // memory only
        }
        return $total;
    }

    public function registerStatusChange(?string $from, string $to, ?int $userId): void
    {
        $this->statusHistories()->create([
            'user_id'=>$userId,
            'from_status'=>$from,
            'to_status'=>$to
        ]);
    }
}
