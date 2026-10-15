<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Retur extends Model
{
    protected $fillable = [
        'customer_id',
        'transaction_id',
        'retur_number',
        'status',
        'note',
        'admin_note',
        'requested_at',
        'approved_at',
        'rejected_at',
        'completed_at',
        'cancelled_at',
        'approved_by'
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Retur $retur) {
            $paths = $retur->items()->pluck('photo_path')->filter()->all();

            if ($paths !== []) {
                Storage::disk('public')->delete($paths);
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function items()
    {
        return $this->hasMany(ReturItem::class);
    }

    public function creditNote()
    {
        return $this->hasOne(CreditNote::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}
