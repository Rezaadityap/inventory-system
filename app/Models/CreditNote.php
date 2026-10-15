<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreditNote extends Model
{
    public const STATUSES = ['open', 'applied', 'refunded', 'replaced'];

    protected $fillable = [
        'retur_id',
        'invoice_id',
        'customer_id',
        'credit_note_number',
        'subtotal',
        'tax',
        'total',
        'status',
        'note',
        'applied_at',
        'refunded_at',
        'refund_proof_path',
        'replaced_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
            'refunded_at' => 'datetime',
            'replaced_at' => 'datetime',
        ];
    }

    public function retur()
    {
        return $this->belongsTo(Retur::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function replacementTransaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function getRefundProofUrlAttribute(): ?string
    {
        return $this->refund_proof_path ? Storage::url($this->refund_proof_path) : null;
    }

    public function isSettled(): bool
    {
        return in_array($this->status, ['applied', 'refunded', 'replaced'], true);
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
                'applied' => 'Dipotong dari Invoice',
                'refunded' => 'Sudah Dikembalikan',
                'replaced' => 'Diganti Barang',
                default => 'Menunggu Pengembalian',
            }
        );
    }
}
