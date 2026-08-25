<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
