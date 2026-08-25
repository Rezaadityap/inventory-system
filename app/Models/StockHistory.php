<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    protected $fillable = [
        'waste_id',
        'transaction_id',
        'type',
        'source',
        'qty',
        'qty_before',
        'qty_after',
        'remark',
        'user_id'
    ];

    public function waste()
    {
        return $this->belongsTo(Waste::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
