@extends('layouts.app')
@section('page-title')
    Dashboard
@endsection
@section('page-subtitle')
    Welcome back, {{ Auth::user()->name }}
@endsection
@section('content')
    @role('pengemudi')
        <div class="w-full space-y-4" x-data="{ viewModal: false, viewData: {} }">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-xl shadow-md flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-warning/10 text-warning flex items-center justify-center shrink-0">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-xs text-text-secondary">Pending</p>
                        <p class="text-xl font-semibold text-text-primary">
                            {{ $todayShipments->where('status', 'Diproses')->count() }}</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <i data-lucide="truck" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-xs text-text-secondary">Dikirim</p>
                        <p class="text-xl font-semibold text-text-primary">
                            {{ $todayShipments->where('status', 'Dikirim')->count() }}</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-success/10 text-success flex items-center justify-center shrink-0">
                        <i data-lucide="circle-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-xs text-text-secondary">Selesai</p>
                        <p class="text-xl font-semibold text-text-primary">
                            {{ $todayShipments->where('status', 'Sampai')->count() }}</p>
                    </div>
                </div>
            </div>

            @if ($todayShipments->isEmpty())
                <div class="bg-white p-6 rounded-xl shadow-md text-center text-text-secondary text-sm">
                    Tidak ada jadwal pengiriman untuk hari ini.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($todayShipments as $shipment)
                        @include('dashboard.shipment-card', ['shipment' => $shipment])
                    @endforeach
                </div>
            @endif

            @include('shipment.view')
        </div>
    @elserole('pelanggan')
        @include('dashboard.customer')
    @elserole('admin')
        @include('dashboard.admin')
    @else
        <div class="w-full bg-white p-5 shadow-md rounded-xl">
            Selamat datang, {{ Auth::user()->name }}
        </div>
    @endrole
@endsection
