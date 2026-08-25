<?php

namespace App\Livewire;

use App\Models\Menu;
use App\Models\Transaction;
use Livewire\Attributes\On;
use Livewire\Component;

class Sidebar extends Component
{
    #[On('menu-saved')]
    #[On('menu-deleted')]
    public function refresh()
    {
        // Re-renders the component so the menu list reflects the latest data.
    }

    public function render()
    {
        $sidebarMenus = Menu::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->filter(fn(Menu $menu) => auth()->user()->can("show-{$menu->code}"))
            ->values();

        $pendingTransactions = Transaction::where('status', 'Pending')->count();

        return view('livewire.sidebar', compact('sidebarMenus', 'pendingTransactions'));
    }
}
