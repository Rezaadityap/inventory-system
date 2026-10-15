<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    public const STATUSES = ['unpaid', 'paid', 'cancelled', 'expired'];

    protected $fillable = [
        'transaction_id',
        'customer_id',
        'invoice_number',
        'subtotal',
        'discount',
        'tax',
        'total',
        'status',
        'due_date',
        'paid_at',
        'snap_token',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime'
        ];
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }

    protected function formattedTotal(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp. ' . number_format($this->total, 0, ',', '.')
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->status) {
                'paid' => 'Lunas',
                'cancelled' => 'Dibatalkan',
                'expired' => 'Kedaluwarsa',
                default => 'Belum Bayar',
            }
        );
    }

    /** Jatuh tempo dihitung sampai akhir harinya, bukan tengah malam awal hari. */
    public function isOverdue(): bool
    {
        return $this->due_date->copy()->endOfDay()->isPast();
    }

    /** Masih boleh dibayar: belum lunas/batal dan belum lewat jatuh tempo. */
    public function isPayable(): bool
    {
        return $this->status === 'unpaid' && ! $this->isOverdue();
    }
}
