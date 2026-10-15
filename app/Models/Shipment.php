<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'transaction_id',
        'driver_id',
        'shipment_number',
        'vehicle_number',
        'status',
        'shipped_by',
        'shipped_at',
        'planned_shipped_at',
        'estimated_arrival',
        'arrived_at',
        'received_at',
        'received_by'
    ];

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'planned_shipped_at' => 'datetime',
            'estimated_arrival' => 'datetime',
            'arrived_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipped_by', 'id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by', 'id');
    }
}
