<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Waste extends Model
{
    protected $fillable = [
        'code',
        'waste_name',
        'unit',
        'unit_price',
        'category_id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp. ' . number_format($this->unit_price, 0, ',', '.')
        );
    }
}
