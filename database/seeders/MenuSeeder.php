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
            ['name' => 'Customers', 'code' => 'customers', 'route' => 'customers.index', 'icon' => 'contact', 'order' => 2],
            ['name' => 'Users', 'code' => 'users', 'route' => 'user.index', 'icon' => 'users', 'order' => 3],
            ['name' => 'Menu', 'code' => 'menu', 'route' => 'menus.index', 'icon' => 'table-of-contents', 'order' => 4],
            ['name' => 'Role', 'code' => 'role', 'route' => 'roles.index', 'icon' => 'shield', 'order' => 5],
        ];

        foreach ($menus as $menu) {
            Menu::firstOrCreate(['code' => $menu['code']], $menu);
        }
    }
}
