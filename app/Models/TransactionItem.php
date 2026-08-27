<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'waste_id',
        'qty',
        'price',
        'note_item'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function waste()
    {
        return $this->belongsTo(Waste::class);
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp. ' . number_format($this->price, 0, ',', '.')
        );
    }
}
