<?php

namespace App\Livewire;

use App\Models\Menu;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class RolePermissionManager extends Component
{
    public Role $role;
    public $selectedPermissions = [];

    public function mount(Role $role)
    {
        $this->role = $role;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
    }

    public function save()
    {
        $this->role->syncPermissions($this->selectedPermissions);

        session()->now('success', 'Permission untuk role "' . $this->role->name . '" berhasil diperbarui');
    }

    public function render()
    {
        $menus = Menu::where('is_active', true)->orderBy('order')->get();
        $actions = ['show' => 'Lihat', 'store' => 'Tambah', 'update' => 'Edit', 'delete' => 'Hapus'];

        $menuGrouped = $menus->map(function ($menu) use ($actions) {
            return [
                'menu_name' => $menu->name,
                'permissions' => collect($actions)
                    ->map(
                        fn($label, $action) => [
                            'name' => "{$action}-{$menu->code}",
                            'label' => $label,
                        ],
                    )
                    ->values(),
            ];
        });

        return view('livewire.role-permission-manager', compact('menuGrouped'))->extends('layouts.app');
    }
}
