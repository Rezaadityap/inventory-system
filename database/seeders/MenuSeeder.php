<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['name' => 'Dashboard', 'code' => 'dashboard', 'route' => 'dashboard', 'icon' => 'layout-grid', 'order' => 0],
            ['name' => 'Kategori', 'code' => 'kategori', 'route' => 'categories.index', 'icon' => 'flask-conical', 'order' => 1],
            ['name' => 'Limbah', 'code' => 'limbah', 'route' => 'wastes.index', 'icon' => 'package', 'order' => 2],
            ['name' => 'Stok', 'code' => 'stok', 'route' => 'stocks.index', 'icon' => 'database', 'order' => 3],
            ['name' => 'Riwayat Stok', 'code' => 'riwayat-stok', 'route' => 'stock-histories.index', 'icon' => 'clock', 'order' => 4],
            ['name' => 'Transaksi', 'code' => 'transaksi', 'route' => 'transactions.index', 'icon' => 'receipt-text', 'order' => 5],
            ['name' => 'Retur', 'code' => 'retur', 'route' => 'returs.index', 'icon' => 'rotate-ccw', 'order' => 6],
            ['name' => 'Invoice', 'code' => 'invoices', 'route' => 'invoices.index', 'icon' => 'file-text', 'order' => 7],
            ['name' => 'Pengiriman', 'code' => 'pengiriman', 'route' => 'shipments.index', 'icon' => 'truck', 'order' => 8],
            ['name' => 'Pengemudi', 'code' => 'pengemudi', 'route' => 'drivers.index', 'icon' => 'user-round', 'order' => 9],
            ['name' => 'Customers', 'code' => 'customers', 'route' => 'customers.index', 'icon' => 'contact', 'order' => 10],
            ['name' => 'Users', 'code' => 'users', 'route' => 'user.index', 'icon' => 'users', 'order' => 11],
            ['name' => 'Menu', 'code' => 'menu', 'route' => 'menus.index', 'icon' => 'table-of-contents', 'order' => 12],
            ['name' => 'Role', 'code' => 'role', 'route' => 'roles.index', 'icon' => 'shield', 'order' => 13],
        ];

        foreach ($menus as $menu) {
            Menu::updateOrCreate(['code' => $menu['code']], $menu);
        }
    }
}
