<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'waste_id',
        'qty'
    ];

    public function waste()
    {
        return $this->belongsTo(Waste::class);
    }
}
