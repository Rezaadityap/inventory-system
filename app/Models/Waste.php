<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Waste extends Model
{
    protected $fillable = [
        'code',
        'waste_name',
        'unit',
        'category_id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
