<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Override;
use Spatie\Permission\Models\Permission;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'code',
        'route',
        'icon',
        'parent_id',
        'order',
        'is_active'
    ];

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    // Ini adalah fungsi yang dijalankan ketika ada menu yang baru dibuat otomatis generate permission dasar
    protected static function booted()
    {
        static::created(function (Menu $menu) {
            $actions = ['show', 'store', 'update', 'delete'];

            foreach ($actions as $item) {
                Permission::firstOrCreate([
                    'name' => "{$item}-{$menu->code}",
                    'guard_name' => 'web'
                ]);
            }
        });

        // Fungsi static untuk menghapus permission
        static::deleting(function (Menu $menu) {
            Permission::where('name', 'like', "%-{$menu->code}")->delete();
        });
    }
}
