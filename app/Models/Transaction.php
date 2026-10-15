<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Transaction extends Model
{
    protected $fillable = [
        'customer_id',
        'credit_note_id',
        'transaction_number',
        'total_price',
        'status',
        'note',
        'admin_note',
        'for_date',
        'requested_at',
        'approved_at',
        'completed_at',
        'cancelled_at',
        'rejected_at',
        'approved_by'
    ];

    protected function casts(): array
    {
        return [
            'for_date' => 'date',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Transaction $transaction) {
            $paths = $transaction->histories()->pluck('photo_path')->filter()->all();

            if ($paths !== []) {
                Storage::disk('public')->delete($paths);
            }
        });
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

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function histories()
    {
        return $this->hasMany(TransactionHistory::class)->latest();
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function isReplacement(): bool
    {
        return $this->credit_note_id !== null;
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp. ' . number_format($this->total_price, 0, ',', '.')
        );
    }
}
