<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'customer_id',
        'transaction_number',
        'total_price',
        'status',
        'note',
        'requested_at',
        'approved_at',
        'completed_at',
        'cancelled_at',
        'approved_by'
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function stockHistories()
    {
        return $this->hasMany(StockHistory::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'id', 'approved_by');
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp. ' . number_format($this->total_price, 0, ',', '.')
        );
    }
}
