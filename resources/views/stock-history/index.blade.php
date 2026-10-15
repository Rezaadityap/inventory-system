@extends('layouts.app')
@section('page-title')
    Riwayat Stok
@endsection
@section('page-subtitle')
    Riwayat transaksi stok masuk dan keluar
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ viewModal: false, viewData: {} }">
        @livewire('stock-history-table')

        @include('stock-history.view')
    </div>
@endsection
