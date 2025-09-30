<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleTransfer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_id',
        'requester_id',
        'origin_secretariat_id',
        'destination_secretariat_id',
        'approver_id',
        'transfer_type',
        'start_date',
        'end_date',
        'status',
        'request_notes',
        'approval_notes',
    ];

    /**
     * Get the vehicle associated with the transfer.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who requested the transfer.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Get the origin secretariat of the transfer.
     */
    public function originSecretariat(): BelongsTo
    {
        return $this->belongsTo(Secretariat::class, 'origin_secretariat_id');
    }

    /**
     * Get the destination secretariat of the transfer.
     */
    public function destinationSecretariat(): BelongsTo
    {
        return $this->belongsTo(Secretariat::class, 'destination_secretariat_id');
    }

    /**
     * Get the user who approved/rejected the transfer.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
