<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'user_id',
        'phone_number',
        'driver_license',
        'available_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
