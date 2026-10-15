<?php

namespace App\Livewire;

use App\Models\Menu;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class MenuManager extends Component
{
    public $menus;

    public function mount()
    {
        $this->loadMenus();
    }

    public function loadMenus()
    {
        $this->menus = Menu::whereNull('parent_id')->with('children')->orderBy('order')->get();
    }

    public function save($id, $name, $code, $route, $icon, $parent_id, $order, $is_active)
    {
        $isEdit = ! empty($id);

        $validated = Validator::make([
            'name' => $name,
            'code' => $code,
            'route' => $route,
            'icon' => $icon,
            'parent_id' => $parent_id ?: null,
            'order' => $order,
            'is_active' => $is_active,
        ], [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|alpha_dash|unique:menus,code' . ($isEdit ? ',' . $id : ''),
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ])->validate();

        if ($isEdit) {
            $menu = Menu::findOrFail($id);
            $oldCode = $menu->code;
            $menu->update($validated);

            if ($oldCode !== $menu->code) {
                $this->renamePermissions($oldCode, $menu->code);
            }

            session()->now('success', 'Menu berhasil diperbarui');
        } else {
            $menu = Menu::create($validated);
            $this->createPermissions($menu->code);
            session()->now('success', 'Menu berhasil ditambahkan');
        }

        $this->loadMenus();
        $this->dispatch('menu-saved');
    }

    public function delete($id)
    {
        Menu::findOrFail($id)->delete();

        session()->now('success', 'Menu berhasil dihapus');
        $this->loadMenus();
        $this->dispatch('menu-deleted');
    }

    private function createPermissions(string $code): void
    {
        foreach (['show', 'store', 'update', 'delete'] as $action) {
            Permission::firstOrCreate(['name' => "{$action}-{$code}", 'guard_name' => 'web']);
        }
    }

    private function renamePermissions(string $oldCode, string $newCode): void
    {
        foreach (['show', 'store', 'update', 'delete'] as $action) {
            $permission = Permission::where('name', "{$action}-{$oldCode}")->first();
            if ($permission) {
                $permission->update(['name' => "{$action}-{$newCode}"]);
            }
        }
    }

    public function render()
    {
        return view('livewire.menu-manager', [
            'parents' => Menu::whereNull('parent_id')->get(),
        ])->extends('layouts.app');
    }
}
