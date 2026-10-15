<?php

namespace App\Livewire;

use App\Models\Menu;
use App\Models\Shipment;
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

        $pendingTransactions = Transaction::where('status', 'Menunggu')->count();

        $processingShipments = auth()->user()->hasRole('pengemudi')
            ? Shipment::where('status', 'Diproses')
                ->whereHas('driver', fn($d) => $d->where('user_id', auth()->id()))
                ->whereDate('planned_shipped_at', '<=', now())
                ->count()
            : 0;

        $arrivedShipments = auth()->user()->hasRole('pelanggan')
            ? Shipment::where('status', 'Sampai')
                ->whereHas('transaction.customer', fn($c) => $c->where('user_id', auth()->id()))
                ->count()
            : 0;

        return view('livewire.sidebar', compact('sidebarMenus', 'pendingTransactions', 'processingShipments', 'arrivedShipments'));
    }
}
