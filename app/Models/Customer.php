<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Customer extends Model
{
    protected $fillable = [
        'customer_name',
        'email',
        'phone_number',
        'company_photo',
        'address',
        'user_id'
    ];

    protected static function booted(): void
    {
        static::deleting(function (Customer $customer) {
            $paths = TransactionHistory::whereIn('transaction_id', $customer->transactions()->select('id'))
                ->whereNotNull('photo_path')
                ->pluck('photo_path')
                ->merge(
                    ReturItem::whereIn('retur_id', $customer->returs()->select('id'))
                        ->whereNotNull('photo_path')
                        ->pluck('photo_path')
                )
                ->all();

            if ($paths !== []) {
                Storage::disk('public')->delete($paths);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function returs()
    {
        return $this->hasMany(Retur::class);
    }
}
