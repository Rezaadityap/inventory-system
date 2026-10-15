<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TransactionHistory extends Model
{
    protected $fillable = [
        'transaction_id',
        'status',
        'description',
        'photo_path',
        'user_id'
    ];

    protected static function booted(): void
    {
        static::deleting(function (TransactionHistory $history) {
            if ($history->photo_path) {
                Storage::disk('public')->delete($history->photo_path);
            }
        });
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
