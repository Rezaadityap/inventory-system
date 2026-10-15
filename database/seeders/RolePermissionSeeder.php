<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Menu::pluck('code') as $code) {
            foreach (['show', 'store', 'update', 'delete'] as $action) {
                Permission::firstOrCreate(['name' => "{$action}-{$code}", 'guard_name' => 'web']);
            }
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(Permission::pluck('name'));

        $pelanggan = Role::firstOrCreate(['name' => 'pelanggan', 'guard_name' => 'web']);
        $pelanggan->givePermissionTo([
            'show-dashboard',
            'show-transaksi',
            'store-transaksi',
            'update-transaksi',
            'show-retur',
            'store-retur',
            'show-invoices',
            'show-pengiriman',
        ]);

        // Pengemudi hanya boleh membuka halaman Pengiriman (jadwal miliknya sendiri).
        $pengemudi = Role::firstOrCreate(['name' => 'pengemudi', 'guard_name' => 'web']);
        $pengemudi->givePermissionTo([
            'show-dashboard',
            'show-pengiriman',
        ]);
    }
}
