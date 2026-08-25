<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class RoleManager extends Component
{
    public $roles;

    public function mount()
    {
        $this->loadRoles();
    }

    public function loadRoles()
    {
        $this->roles = Role::withCount('permissions')->get();
    }

    public function save($id, $name)
    {
        $isEdit = ! empty($id);

        $validated = Validator::make([
            'name' => $name,
        ], [
            'name' => 'required|string|max:255|unique:roles,name' . ($isEdit ? ',' . $id : ''),
        ])->validate();

        if ($isEdit) {
            Role::findOrFail($id)->update($validated);
            session()->now('success', 'Role berhasil diperbarui');
        } else {
            Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
            session()->now('success', 'Role berhasil dibuat');
        }

        $this->loadRoles();
        $this->dispatch('role-saved');
    }

    public function delete($id)
    {
        Role::findOrFail($id)->delete();

        session()->now('success', 'Role berhasil dihapus');
        $this->loadRoles();
        $this->dispatch('role-deleted');
    }

    public function render()
    {
        return view('livewire.role-manager')->extends('layouts.app');
    }
}
