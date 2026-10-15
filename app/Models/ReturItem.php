<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReturItem extends Model
{
    protected $fillable = [
        'retur_id',
        'transaction_item_id',
        'waste_id',
        'qty',
        'price',
        'note_item',
        'photo_path'
    ];

    public function retur()
    {
        return $this->belongsTo(Retur::class);
    }

    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class);
    }

    public function waste()
    {
        return $this->belongsTo(Waste::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }
}
