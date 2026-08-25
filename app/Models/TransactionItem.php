<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'waste_id',
        'qty',
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
}
